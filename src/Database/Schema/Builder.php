<?php
namespace API\Database\Schema;

use API\Database\AppContext as AppContext;

/**
 * Builder
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Builder
{

    /**
     * dynamic
     *
     * @param API\Model\Collection $model
     * @param mixed $attributes
     */
    public static function dynamic($model, &$attributes = null)
    {
        $connection = $model->getConnectionResolver()->connection();

        // Try to migrate schema.
        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        $builder = $connection->getSchemaBuilder();

        $table = $model->getTable();
        $table_name = AppContext::getPrefix() . $table;

        $is_creating = (!$builder->hasTable($table));
        $method = ($is_creating) ? 'create' : 'table';

        $migrate = function ($t) use (&$table, &$builder, &$is_creating, &$attributes) {
            if ($is_creating) {
                // primary key
                $t->increments('_id');

                // deleted_at field
                $t->softDeletes();

                // created_at / updated_at field
                $t->timestamps();
            }

            $ignore_fields = array('created_at', 'updated_at', 'deleted_at');
            foreach ($attributes as $field => $value) {
                // ignore fields
                if (in_array($field, $ignore_fields)) {
                    continue;
                }

                // ignore fields that already exists on 'update'
                if (!$is_creating) {
                    if ($builder->hasColumn($table, $field) || $builder->hasColumn($table, "`{$field}`")) {
                        continue;
                    }
                }

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
                    $t->{$datatype}($field)->nullable();
                }
            }
        };

        return call_user_func(array($builder, $method), $table_name, $migrate);
    }

    /**
     * migrate
     *
     * @param API\Model\Collection $model
     * @param array $config
     *
     * @return bool
     */
    public static function migrate($model, $config)
    {
        $connection = $model->getConnectionResolver()->connection();

        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        $builder = $connection->getSchemaBuilder();

        // Set custom blueprint resolver
        $builder->blueprintResolver(function($table, $callback) {
            return new \API\Database\Schema\Blueprint($table, $callback);
        });

        $table = $model->getTable();
        $table_name = AppContext::getPrefix() . $table;

        $is_creating = (!$builder->hasTable($table));
        $method = ($is_creating) ? 'create' : 'table';

        $cached_schema = Cache::get($table);

        if (isset($config['attributes'])) {
            $attributes = $config['attributes'];
            $migrate = function ($t) use (&$table, &$builder, &$is_creating, $attributes, &$cached_schema) {
                if ($is_creating) {
                    $t->increments('_id'); // primary key
                    $t->timestamps();      // created_at / updated_at field
                    $t->softDeletes();     // deleted_at field
                }

                foreach($attributes as $attribute) {

                    if (!isset($attribute['type']) || !isset($attribute['name'])) {
                        throw new API\Exceptions\MethodFailureException('invalid_schema');
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

                    if (count($attribute) > 0) {
                        // the remaining attributes on field definition are
                        // the data-type related config, such as 'length',
                        // 'allowed', 'total', 'places', etc.
                        $column = $t->newColumn($type, $field_name, $attribute);
                    } else {
                        $column = $t->{$type}($field_name);
                    }

                    // columns are nullable unless specified as 'required'
                    if (!$required) { $column->nullable(); }

                    // apply default value
                    if ($default) { $column->default($default); }

                    // apply index if specified
                    if ($index && !$unique) { $column->index(); }

                    // apply unique index if specified
                    if ($unique) {
                        $unique_fields = (!is_array($unique)) ? $field_name : array_merge(array($field_name), $unique);
                        $t->unique($unique_fields);
                    }
                }
            };
        }

        Cache::set($table, $config);
        return call_user_func(array($builder, $method), $table_name, $migrate);
    }

}

// $table->bigIncrements('id');	Incrementing ID using a "big integer" equivalent.
// $table->bigInteger('votes');	BIGINT equivalent to the table
// $table->binary('data');	BLOB equivalent to the table
// $table->boolean('confirmed');	BOOLEAN equivalent to the table
// $table->char('name', 4);	CHAR equivalent with a length
// $table->date('created_at');	DATE equivalent to the table
// $table->dateTime('created_at');	DATETIME equivalent to the table
// $table->decimal('amount', 5, 2);	DECIMAL equivalent with a precision and scale
// $table->double('column', 15, 8);	DOUBLE equivalent with precision
// $table->enum('choices', array('foo', 'bar'));	ENUM equivalent to the table
// $table->float('amount');	FLOAT equivalent to the table
// $table->increments('id');	Incrementing ID to the table (primary key).
// $table->integer('votes');	INTEGER equivalent to the table
// $table->longText('description');	LONGTEXT equivalent to the table
// $table->mediumText('description');	MEDIUMTEXT equivalent to the table
// $table->morphs('taggable');	Adds INTEGER taggable_id and STRING taggable_type
// $table->smallInteger('votes');	SMALLINT equivalent to the table
// $table->tinyInteger('numbers');	TINYINT equivalent to the table
// $table->softDeletes();	Adds deleted_at column for soft deletes
// $table->string('email');	VARCHAR equivalent column
// $table->string('name', 100);	VARCHAR equivalent with a length
// $table->text('description');	TEXT equivalent to the table
// $table->time('sunrise');	TIME equivalent to the table
// $table->timestamp('added_on');	TIMESTAMP equivalent to the table
// $table->timestamps();	Adds created_at and updated_at columns
// ->nullable()	Designate that the column allows NULL values
// ->default($value)	Declare a default value for a column
// ->unsigned()
