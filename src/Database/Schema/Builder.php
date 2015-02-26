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

    /**
     * isSupported
     *
     * @return bool
     */
    public static function isSupported() {
        $connection = \DLModel::getConnectionResolver()->connection();
        return !is_null($connection->getPdo());
    }

    /**
     * dump
     *
     * @return array
     */
    public static function dump() {
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
    public static function dynamic($model, &$attributes = null)
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
            return static::migrate($model, $config);
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
    public static function migrate($model, $collection_config)
    {
        $result = false;
        $connection = $model->getConnectionResolver()->connection();

        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        // Get modified Schema\Grammar for hook features.
        $connection->setSchemaGrammar(static::getSchemaGrammar($connection));

        // Set custom blueprint resolver
        $builder = $connection->getSchemaBuilder();
        $builder->blueprintResolver(function($table, $callback) {
            return new \Hook\Database\Schema\Blueprint($table, $callback);
        });

        $table = $model->getTable();
        $table_schema = Cache::get($table);
        $table_prefix = Context::getPrefix();
        $collection_config = static::sanitizeConfig($table, $collection_config);

        $is_creating = (!$builder->hasTable($table));

        if (!empty($collection_config['attributes']) || !empty($collection_config['relationships'])) {
            $migrate = function ($t) use (&$table, &$table_prefix, &$builder, &$is_creating, &$table_schema, $collection_config, &$result) {
                if ($is_creating) {
                    $t->increments('_id'); // primary key
                    $t->timestamps();      // created_at / updated_at field
                    $t->softDeletes();     // deleted_at field
                    $table_columns = array();

                } else {
                    $table_columns = $builder->getColumnListing($table);
                }

                foreach($collection_config['attributes'] as $attribute) {
                    if (!isset($attribute['name'])) {
                        throw new MethodFailureException('invalid_schema');
                    }

                    $field_name = strtolower(array_remove($attribute, 'name'));
                    $type = camel_case(array_remove($attribute, 'type') ?: 'string');

                    $default = array_remove($attribute, 'default');
                    $index = array_remove($attribute, 'index');
                    $unique = array_remove($attribute, 'unique') || $index == 'unique';
                    $required = array_remove($attribute, 'required');

                    // Skip default fields
                    $ignore_fields = array('created_at', 'updated_at', 'deleted_at');
                    if (in_array($field_name, $ignore_fields)) {
                        continue;
                    }

                    // Skip if column already exists
                    // TODO: deprecate strtolower
                    if (in_array($field_name, array_map('strtolower', $table_columns))) {
                        continue;
                    }

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

                    if ($index == 'spatial') {
                        // apply geospatial index, only MyISQL
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
                            // TODO: dry with 'is_creating' on 'migrate' function
                            if (!$builder->hasTable($config['collection'])) {
                                $builder->create($table_prefix . $config['collection'], function($t) {
                                    $t->increments('_id'); // primary key
                                    $t->timestamps();      // created_at / updated_at field
                                    $t->softDeletes();     // deleted_at field
                                });
                            }

                            // create foreign key on database
                            $t->foreign($config['foreign_key'])
                                ->references($config['primary_key'])
                                ->on($table_prefix . $config['collection'])
                                ->onDelete($actions[$config['on_delete']])
                                ->onUpdate($actions[$config['on_update']]);
                        }
                    }
                }

                // return true when any modification is present
                if (count($t->getColumns()) > 0 || count($t->getCommands()) > 0) {
                    $result = true;
                }

            };

            $method = ($is_creating) ? 'create' : 'table';
            call_user_func(array($builder, $method), $table_prefix . $table, $migrate);
        }

        // Cache table schema for further reference
        $table_schema = array_merge_recursive($table_schema, $collection_config);
        Cache::forever($table, $table_schema);

        $app_collections = Cache::get('app_collections');
        Cache::forever('app_collections', array_unique(array_merge($app_collections, array($table))));

        return $result;
    }

    protected static function getSchemaGrammar($connection)
    {
        $connection_klass = get_class($connection);
        $connection_klass = str_replace('Illuminate\\Database', '', $connection_klass);
        $connection_klass = 'Hook\\Database\\Schema\\Grammars' . str_replace('Connection', 'Grammar', $connection_klass);
        return new $connection_klass;
    }

    protected static function sanitizeConfig($collection_name, &$config) {
        if (!isset($config['relationships'])) {
            $config['relationships'] = array();
        }

        if (!isset($config['attributes'])) {
            $config['attributes'] = array();
        }

        // sanitize relationship definitions
        foreach($config['relationships'] as $relation => $fields) {
            $config['relationships'][$relation] = Relation::sanitize($collection_name, $relation, $fields);
        }

        return $config;
    }

    public static function __callStatic($method, $args)
    {
        $connection = \DLModel::getConnectionResolver()->connection();
        $builder = $connection->getSchemaBuilder();
        return call_user_func_array(array($builder, $method), $args);
    }

}
