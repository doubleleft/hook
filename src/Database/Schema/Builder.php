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
            $migrate = function ($t) use ($that, &$table, &$table_prefix, &$builder, &$is_creating, &$table_schema, $collection_config, &$result, &$connection) {
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

                    $previous_attribute_definition = null;
                    if (isset($table_schema['attributes'])) {
                        $previous_attribute_definition = current(array_filter($table_schema['attributes'], function ($column) use (&$field_name) {
                            return ($column['name'] === $field_name);
                        }));
                    }

                    // fix core PHP to database types.
                    if ($type == 'double') { $type = 'float'; }

                    // fix ENUM reference in order to doctrine migrations to work
                    if ($type == 'enum') {
                        $allowed = (isset($attribute['allowed'])) ? $attribute['allowed'] : null;
                        $this->registerEnumDoctrineType($table, $field_name, $allowed);

                    } else if (isset($previous_attribute_definition['type']) && $previous_attribute_definition['type'] == 'enum') {
                        $allowed = (isset($previous_attribute_definition['allowed'])) ? $previous_attribute_definition['allowed'] : null;
                        $this->registerEnumDoctrineType($table, $field_name, $allowed);
                    }

                    $default = array_remove($attribute, 'default');
                    $index = array_remove($attribute, 'index');
                    $unique = array_remove($attribute, 'unique') || $index === 'unique';
                    $required = array_remove($attribute, 'required');

                    //
                    // TODO: dropping indexes is not working yet
                    //
                    // // Remove indexes, if needed
                    // $this->dropIndex($table, $table_prefix, $t, $previous_attribute_definition, $index, $unique);

                    // Skip if column already exists and haven't changed
                    if (isset($previous_attribute_definition['type']) && $previous_attribute_definition['type'] === $type) {
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

    /**
     * Drop index from field if necessary.
     *
     * Compare previous attribute definition with new ones and drop necessary
     * indexes.
     *
     * @param   string    $table_name
     * @param   Blueprint $blueprint
     * @param   array     $previous_attribute_definition
     * @param   string    $index new index type
     * @param   boolean   $is_unique is a unique index?
     *
     * @return bool
     */
    protected function dropIndex(&$table_name, &$table_prefix, &$blueprint, $previous_attribute_definition = null, $index = null, $is_unique = false) {
        if (!$previous_attribute_definition) {
            return;
        }

        // drop unique
        if (!$is_unique && (
                (isset($previous_attribute_definition['index']) && $previous_attribute_definition['index'] == 'unique') ||
                (isset($previous_attribute_definition['unique']) && $previous_attribute_definition['unique'] == true))
        ) {
            // drop unique index
            $blueprint->dropUnique($table_prefix . $table_name . '_' . $previous_attribute_definition['name'] . '_unique');
        }

        if (!$index && (isset($previous_attribute_definition['index']) && $previous_attribute_definition['index'] !== 'unique')) {
            // drop basic index
            $blueprint->dropIndex($table_prefix . $table_name . '_' . $previous_attribute_definition['name'] . '_index');
        }
    }

    protected function sanitizeConfigs($collection_name, &$config, $is_dynamic = false) {
        if (!isset($config['attributes'])) {
            $config['attributes'] = array();
        }

        //
        // ignore built-in fields if defined on schema.yaml
        //
        // - created_at
        // - updated_at
        // - deleted_at
        //
        $config['attributes'] = array_filter($config['attributes'], function($attribute) {
            return !(in_array($attribute['name'], array('created_at', 'updated_at', 'deleted_at')));
        });

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

    protected function registerEnumDoctrineType($table, $field_name, $allowed = array()) {
        //
        // WORKAROUND:
        // register unsupported ENUM as a doctrine supported type
        // related issue: https://github.com/laravel/framework/issues/1186
        //
        $enum_klass_name = 'Enum' . ucfirst($table) . ucfirst($field_name);

        if (!$allowed || empty($allowed)) {
            throw new MethodFailureException('Missing "allowed" values for ENUM type: ' . $table . '#' . $field_name . '.');
        }

        $allowed_json = json_encode($allowed);
        $enum_name = 'enum'; // . $field_name;
        eval('class ' . $enum_klass_name . ' extends Hook\\Database\\Types\\EnumType {
                protected $name = \'' . $enum_name. '\';
                protected $values = array('.substr($allowed_json, 1, strlen($allowed_json) - 2).');
        }');

        $type_method = 'addType';
        if (\Doctrine\DBAL\Types\Type::hasType('enum')) {
            $type_method = 'overrideType';
        }

        call_user_func_array(array('\\Doctrine\\DBAL\\Types\\Type', $type_method), array('enum', $enum_klass_name));

        //
        // WORKAROUND:
        // Need to register 'enum' on AbstractPlatform with anything different
        // than the real type, in order find diffs via:
        // `Illuminate\Database\Schema\Grammars\Grammar#compileChange`
        //
        $connection = \DLModel::getConnectionResolver()->connection();
        $connection->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'blob');

        return $enum_klass_name;
    }

}
