<?php namespace Hook\Database\Schema;

use Hook\Application\Context;
use Carbon\Carbon;

use Hook\Exceptions\MethodFailureException;

/**
 * Builder
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Builder
{
    protected static $instance;

    /**
     * Get schema builder instance.
     *
     * @static
     * @return bool
     */
    public static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * isSupported
     *
     * @return bool
     */
    public function isSupported() {
        $connection = \DLModel::getConnectionResolver()->connection();
        return !is_null($connection->getPdo());
    }

    /**
     * dump
     *
     * @return array
     */
    public function dump() {
        $schema = array();

        foreach(Cache::get('app_collections') as $collection) {
            $schema[$collection] = Cache::get($collection);
        }

        return $schema;
    }

    /**
     * dynamic
     *
     * @param Hook\Model\Collection $model
     * @param mixed $attributes
     */
    public function dynamic($model, &$attributes = null)
    {
        // dynamic migration is not allowed when attributes are locked explicitly
        $table_schema = Cache::get($model->getTable());

        if (isset($table_schema['lock_attributes']) && $table_schema['lock_attributes']) {
            return true;
        }

        // Cached attributes
        $cached_attributes = array_map(function($item) { return $item['name']; }, isset($table_schema['attributes']) ? $table_schema['attributes'] : array());

        $config = array(
            'lock_attributes' => false,
            'attributes' => array()
        );

        foreach ($attributes as $field => $value) {
            // extract datatype from field value to migrate
            $datatype = strtolower(gettype($value));

            // ignore 'null' data-types
            if ($datatype == 'null') {
                unset($attributes[$field]);
                continue;
            }

            // Detect large text blocks to declare 'text' datatype.
            if ($datatype == 'string' && strlen($value) > 255) {
                $datatype = 'text';
            }

            if ($datatype !== 'array' && !in_array($field, $cached_attributes)) {
                $config['attributes'][] = array('name' => $field, 'type' => $datatype);
            }
        }

        if (count($config['attributes']) > 0) {
            return $this->migrate($model, $config, true);
        } else {
            return false;
        }
    }

    /**
     * migrate
     *
     * @param Hook\Model\Collection $model
     * @param array $collection_config
     *
     * @return bool
     */
    public function migrate($model, $collection_config, $is_dynamic = false)
    {
        $that = $this;

        $result = false;
        $connection = $model->getConnectionResolver()->connection();

        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        // Get modified Schema\Grammar for hook features.
        $connection->setSchemaGrammar($this->getSchemaGrammar($connection));

        // Set custom blueprint resolver
        $builder = $connection->getSchemaBuilder();
        $builder->blueprintResolver(function($table, $callback) {
            return new \Hook\Database\Schema\Blueprint($table, $callback);
        });

        $table = $model->getTable();
        $table_schema = Cache::get($table);
        $table_prefix = Context::getPrefix();
        $collection_config = $this->sanitizeConfigs($table, $collection_config, $is_dynamic);

        $is_creating = (!$builder->hasTable($table));

        if (!empty($collection_config['attributes']) || !empty($collection_config['relationships'])) {
            $migrate = function ($t) use ($that, &$table, &$table_prefix, &$builder, &$is_creating, &$table_schema, $collection_config, &$result) {
                $table_columns = array('created_at', 'updated_at', 'deleted_at');

                if ($is_creating) {
                    $that->createCollection($t);
                } else {
                    $table_columns = array_merge($table_columns, $builder->getColumnListing($table));
                }

                foreach($collection_config['attributes'] as $attribute) {

                    if (!isset($attribute['name'])) {
                        throw new MethodFailureException('invalid_schema');
                    }

                    $field_name = strtolower(array_remove($attribute, 'name'));
                    $type = camel_case(array_remove($attribute, 'type') ?: 'string');

                    // fix core PHP to database types.
                    if ($type == 'double') {
                        $type = 'float';
                    }

                    $default = array_remove($attribute, 'default');
                    $index = array_remove($attribute, 'index');
                    $unique = array_remove($attribute, 'unique') || $index === 'unique';
                    $required = array_remove($attribute, 'required');

                    // Skip if column already exists
                    // TODO: deprecate strtolower
                    if (in_array($field_name, array_map('strtolower', $table_columns))) {
                        continue;
                    }

                    $column_exists = (!$is_creating && in_array($field_name, array_map('strtolower', $table_columns)));

                    // include field_name to list of collection columns
                    array_push($table_columns, $field_name);

                    if (count($attribute) > 0) {
                        // the remaining attributes on field definition are
                        // the data-type related collection_config, such as 'length',
                        // 'allowed', 'total', 'places', etc.
                        $column = $t->newColumn($type, $field_name, $attribute);
                    } else {
                        $column = $t->{$type}($field_name);
                    }

                    // apply default value
                    if ($default !== NULL) {
                        $required = true;
                        $column->default($default);
                    }

                    // spatial indexes are NOT NULL by default
                    $nullable = !$required && ($type !== 'point');

                    // columns are nullable unless specified as 'required'
                    if ($nullable) { $column->nullable(); }

                    // apply change if column already exists (MODIFY statement)
                    if ($column_exists) { $column->change(); }

                    if ($index == 'spatial') {
                        // apply geospatial index, only MyISAM
                        $t->spatialIndex($field_name);
                    } else if ($index && !$unique) {
                        // apply index if specified
                        $column->index();
                    }

                    if ($unique) {
                        // apply unique index if specified
                        $unique_fields = (!is_array($unique)) ? $field_name : array_unique(array_merge(array($field_name), $unique));
                        $t->unique($unique_fields);
                    }
                }

                // onDelete / onUpdate actions
                $actions = array(
                    'restrict' => "RESTRICT",
                    'cascade' => "CASCADE",
                    'none' => "NO ACTION",
                    'null' => "SET NULL",
                    'default' => "SET DEFAULT"
                );

                if (!isset($collection_config['relationships'])) {
                    $collection_config['relationships'] = array();
                }

                foreach($collection_config['relationships'] as $relation => $fields) {
                    // only create field on belongs_to relationships
                    if ($relation == "belongs_to") {
                        foreach ($fields as $field => $config) {
                            // create 'foreign_key' column on collection.
                            if (!in_array($config['foreign_key'], array_map('strtolower', $table_columns))) {
                                $column = $t->unsignedInteger($config['foreign_key']);
                                $column->nullable();
                            }

                            // create collection if it doesn't exists
                            if (!$builder->hasTable($config['collection'])) {
                                $builder->create($table_prefix . $config['collection'], function($t) use ($that) {
                                    $that->createCollection($t);
                                });
                            }

                            // //
                            // // create foreign key on database
                            // //
                            // // TODO: list foreign keys already defined before
                            // // trying to create it.
                            // //
                            // $t->foreign($config['foreign_key'])
                            //     ->references($config['primary_key'])
                            //     ->on($table_prefix . $config['collection'])
                            //     ->onDelete($actions[$config['on_delete']])
                            //     ->onUpdate($actions[$config['on_update']]);
                        }
                    }
                }

                // return true when any modification is present
                if (count($t->getColumns()) > 0 || count($t->getCommands()) > 0) {
                    $result = true;
                }

            };

            if ($is_creating) {
                // CREATE TABLE statement
                $builder->create($table_prefix . $table, $migrate);

            } else {
                // ALTER TABLE statement.
                $builder->table($table_prefix . $table, $migrate);
            }

        }

        // merge previous schema with new one.
        $table_schema = $this->mergeSchema($table_schema, $collection_config, $is_dynamic);

        // Cache table schema for further reference
        Cache::forever($table, $table_schema);

        $app_collections = Cache::get('app_collections');
        Cache::forever('app_collections', array_unique(array_merge($app_collections, array($table))));

        return $result;
    }

    public function createCollection($blueprint)
    {
        $blueprint->increments('_id'); // primary key
        $blueprint->timestamps();      // created_at / updated_at field
        $blueprint->softDeletes();     // deleted_at field
    }

    protected function getSchemaGrammar($connection)
    {
        $connection_klass = get_class($connection);
        $connection_klass = str_replace('Illuminate\\Database', '', $connection_klass);
        $connection_klass = 'Hook\\Database\\Schema\\Grammars' . str_replace('Connection', 'Grammar', $connection_klass);
        return new $connection_klass;
    }

    protected function mergeSchema($table_schema, $collection_config, $is_dynamic = false) {
        if (!isset($table_schema['attributes'])) { $table_schema['attributes'] = array(); }
        if (!isset($table_schema['relationships'])) { $table_schema['relationships'] = array(); }

        $map_name = function($attribute) { return $attribute['name']; };

        $previous_attribute_names = array_map($map_name, $table_schema['attributes']);
        $new_attribute_names = array_map($map_name, $collection_config['attributes']);

        // keep lock_attributes state
        $lock_attributes = (isset($collection_config['lock_attributes']))
            ? $collection_config['lock_attributes']
            : ((isset($table_schema['lock_attributes'])) ? $table_schema['lock_attributes'] : false);

        // add new attributes to table_schema
        if (isset($collection_config['attributes'])) {
            foreach ($collection_config['attributes'] as $new_attribute) {
                $existing_key = array_search($new_attribute['name'], $previous_attribute_names);
                // update existing attribute definition
                if ($existing_key !== false) {
                    $table_schema['attributes'][$existing_key] = $new_attribute;
                } else {
                    // add new attribute definition
                    array_push($table_schema['attributes'], $new_attribute);
                }
            }
        }

        // add new relationships to table_schema
        if (isset($collection_config['relationships'])) {
            $table_schema['relationships'] = $collection_config['relationships'];
        }

        //
        // remove attributes when doing a full migration
        //
        if (!$is_dynamic) {
            $attributes_to_remove = array_diff($previous_attribute_names, $new_attribute_names);
            foreach($attributes_to_remove as $attribute_to_remove)  {
                $index = array_search($attribute_to_remove, $previous_attribute_names);
                unset($table_schema['attributes'][$index]);
            }
            // normalize array keys
            $table_schema['attributes'] = array_values($table_schema['attributes']);
        }

        $table_schema['lock_attributes'] = $lock_attributes;

        return $table_schema;
    }

    protected function sanitizeConfigs($collection_name, &$config, $is_dynamic = false) {
        if (!isset($config['attributes'])) {
            $config['attributes'] = array();
        }

        if (!$is_dynamic) {
            $this->sanitizeRelationships($collection_name, $config);
        }

        return $config;
    }

    protected function sanitizeRelationships($collection_name, &$config) {
        if (!isset($config['relationships'])) { $config['relationships'] = array(); }

        // sanitize relationship definitions
        foreach($config['relationships'] as $relation => $fields) {
            $config['relationships'][$relation] = Relation::sanitize($collection_name, $relation, $fields);
        }

        return $config;
    }

}
