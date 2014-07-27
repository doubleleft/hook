<?php
namespace models;

class DynamicModel extends \Core\Model
{
	protected static $booted = array();

	protected $observables = array('updating_multiple', 'deleting_multiple');

	protected static function registerDefaultEvents($table=null)
	{
		if (is_null($table)) {
			// register events using class name
			static::saving(function ($model) { $model->beforeSave(); });
			static::saved(function ($model) { $model->afterSave(); });
			static::creating(function ($model) { $model->beforeCreate(); });

		} else if (!isset(static::$booted[ $table ])) {

			// register events using table name
			static::$booted[ $table ] = true;
			static::saving(function ($model) { $model->beforeSave(); });
			static::saved(function ($model) { $model->afterSave(); });
			static::creating(function ($model) { $model->beforeCreate(); });
		}
	}

	public function afterSave() {}
	public function beforeCreate() {}

	public function beforeSave() {
		$connection = $this->getConnectionResolver()->connection();

		// Try to migrate schema.
		// Ignore NoSQL databases.
		if (!$connection->getPdo()) { return; }

		$builder = $connection->getSchemaBuilder();
		$attributes = &$this->attributes;

		//
		// TODO: Cache table structure for hasTable/hasColumn boosting
		//
		$table = $this->getTable();

		// Collection table doesn't exists yet: CREATE TABLE
		if (!$builder->hasTable($table)) {

			$builder->create($table, function($t) use (&$attributes) {
				$t->increments('_id');
				foreach($attributes as $field => $value) {
					$datatype = strtolower(gettype($value));
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

				// Create timestamp created_at/updated_at fields if it isn't already defined
				if (!isset($attributes['created_at'])) { $t->integer('created_at'); }
				if (!isset($attributes['updated_at'])) { $t->integer('updated_at'); }

			});

		} else {

			// Add missing fields: ALTER TABLE.
			// TODO: DRY
			$builder->table($table, function($t) use (&$attributes, $builder, $table) {
				foreach($attributes as $field => $value) {
					if (!$builder->hasColumn($table, $field) &&
							!$builder->hasColumn($table, "`{$field}`")) {
						$datatype = strtolower(gettype($value));
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
				}
			});

		}
	}

}
