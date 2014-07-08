<?php namespace Hook\Database\Schema;

use Hook\Database\AppContext as AppContext;

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
        $config = array('attributes');

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

            if ($datatype !== 'array') {
                $config['attributes'][] = array('name' => $field, 'type' => $datatype);
            }
        }

        return static::migrate($model, $config);
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
        $result = null;
        $connection = $model->getConnectionResolver()->connection();

        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        $builder = $connection->getSchemaBuilder();

        // Set custom blueprint resolver
        $builder->blueprintResolver(function($table, $callback) {
            return new \Hook\Database\Schema\Blueprint($table, $callback);
        });

        $table = $model->getTable();
        $table_prefix = AppContext::getPrefix();

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

        if (!isset($config['attributes'])) { $config['attributes'] = array(); }

        if (!empty($config['attributes']) || !empty($config['relationships'])) {
            $migrate = function ($t) use (&$table, &$table_prefix, &$builder, &$is_creating, &$table_schema, $config) {
                if ($is_creating) {
                    $t->increments('_id'); // primary key
                    $t->timestamps();      // created_at / updated_at field
                    $t->softDeletes();     // deleted_at field
                }

                foreach($config['attributes'] as $attribute) {

                    if (!isset($attribute['type']) || !isset($attribute['name'])) {
                        throw new Hook\Exceptions\MethodFailureException('invalid_schema');
                    }

                    $field_name = array_remove($attribute, 'name');
                    $type = camel_case(array_remove($attribute, 'type'));

                    $default = array_remove($attribute, 'default');
                    $index = array_remove($attribute, 'index');
                    $unique = array_remove($attribute, 'unique');
                    $required = array_remove($attribute, 'required');

                    // don't migrate default fields
                    $ignore_fields = array('created_at', 'updated_at', 'deleted_at');
                    if (in_array($field_name, $ignore_fields)) { continue; }

                    if (!$is_creating && ($builder->hasColumn($table, $field_name) ||
                                          $builder->hasColumn($table, "`{$field_name}`"))) {
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
                    if (!$required) { $column->nullable(); }

                    // apply index if specified
                    if ($index && !$unique) { $column->index(); }

                    // apply unique index if specified
                    if ($unique) {
                        $unique_fields = (!is_array($unique)) ? $field_name : array_merge(array($field_name), $unique);
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

            };
            $result = call_user_func(array($builder, $method), $table_prefix . $table, $migrate);
        }


        // Cache table schema for further reference
        $table_schema = array_merge_recursive($table_schema, $config);
        Cache::forever($table, $table_schema);

        $app_collections = Cache::get('app_collections');
        Cache::forever('app_collections', array_unique(array_merge($app_collections, array($table))));


        return $result;
    }

}
