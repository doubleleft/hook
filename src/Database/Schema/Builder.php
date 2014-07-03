<?php
namespace API\Database\Schema;

/**
 * Builder
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Builder
{

    public static function dynamic($model, &$attributes = null)
    {
        $connection = $model->getConnectionResolver()->connection();

        // Try to migrate schema.
        // Ignore NoSQL databases.
        if (!$connection->getPdo()) { return; }

        $builder = $connection->getSchemaBuilder();

        //
        // TODO: Cache table structure for hasTable/hasColumn boosting
        //
        $table = $model->getTable();
        $table_name = $connection->getTablePrefix() . $table;

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
