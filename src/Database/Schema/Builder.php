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
            'dynamic' => true,
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
     * @param array $config
     *
     * @return bool
     */
    public static function migrate($model, $config)
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
        $table_prefix = Context::getPrefix();

        $is_creating = (!$builder->hasTable($table));
        $method = ($is_creating) ? 'create' : 'table';

        $table_schema = Cache::get($table);

        // sanitize / normalize relationship definitions
        if (isset($config['relationships'])) {
            foreach($config['relationships'] as $relation => $fields) {
                $config['relationships'][$relation] = Relation::sanitize($relation, $fields);
            }
        } else {
            $config['relationships'] = array();
        }

        if (!isset($config['attributes'])) {
            $config['attributes'] = array();
        }

        if (!empty($config['attributes']) || !empty($config['relationships'])) {
            $migrate = function ($t) use (&$table, &$table_prefix, &$builder, &$is_creating, &$table_schema, $config, &$result) {
                if ($is_creating) {
                    $t->increments('_id'); // primary key
                    $t->timestamps();      // created_at / updated_at field
                    $t->softDeletes();     // deleted_at field
                } else {
                    $table_columns = $builder->getColumnListing($table);
                }

                foreach($config['attributes'] as $attribute) {
                    if (!isset($attribute['name'])) {
                        throw new MethodFailureException('invalid_schema');
                    }

                    $field_name = strtolower(array_remove($attribute, 'name'));
                    $type = camel_case(array_remove($attribute, 'type') ?: 'string');

                    $default = array_remove($attribute, 'default');
                    $index = array_remove($attribute, 'index');
                    $unique = array_remove($attribute, 'unique') || $index == 'unique';
                    $required = array_remove($attribute, 'required');

                    // spatial indexes are NOT NULL by default
                    $nullable = !$required && ($type !== 'point');

                    // Skip default fields
                    $ignore_fields = array('created_at', 'updated_at', 'deleted_at');
                    if (in_array($field_name, $ignore_fields)) {
                        continue;
                    }

                    // Skip if column already exists
                    if (!$is_creating && in_array($field_name, array_map('strtolower', $table_columns))) {
                        continue;
                    }

                    if (count($attribute) > 0) {
                        // the remaining attributes on field definition are
                        // the data-type related config, such as 'length',
                        // 'allowed', 'total', 'places', etc.
                        $column = $t->newColumn($type, $field_name, $attribute);
                    } else {
                        $column = $t->{$type}($field_name);
                    }

                    // apply default value
                    if ($default) {
                        $required = true;
                        $column->default($default);
                    }

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

                foreach($config['relationships'] as $relation => $fields) {
                    // only create field on belongs_to relationships
                    if ($relation == "belongs_to") {
                        foreach ($fields as $field => $collection) {
                            $foreign_field = $field . '_id';

                            // skip if field already exists
                            if ($builder->hasColumn($table, $foreign_field)) {
                                continue;
                            }

                            // maybe 'collection' table isn't created here.
                            // TODO: create related table before referencing foreign key.
                            $t->unsignedInteger($foreign_field)->index();

                            // $t->foreign($foreign_field)
                            //     ->references('_id')
                            //     ->on($table_prefix . $collection);
                        }
                    }
                }

                // return true when any modification is present
                if (count($t->getColumns()) > 0 || count($t->getCommands()) > 0) {
                    $result = true;
                }

            };

            call_user_func(array($builder, $method), $table_prefix . $table, $migrate);
        }

        // Cache table schema for further reference
        $table_schema = array_merge_recursive($table_schema, $config);
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

    public static function __callStatic($method, $args)
    {
        $connection = \DLModel::getConnectionResolver()->connection();
        $builder = $connection->getSchemaBuilder();
        return call_user_func_array(array($builder, $method), $args);
    }

}
