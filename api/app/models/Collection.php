<?php
namespace Models;

class Collection extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public function __construct(array $attributes = array()) {
		if (isset($attributes['table_name'])) {
			$this->setTable($attributes['table_name']);
			unset($attributes['table_name']);
		}
		parent::__construct($attributes);
	}

	public static function boot() {
		static::saving(function($model) { $model->beforeSave(); });
	}

	public function app() {
		return $this->belongsTo('Models\App');
	}

	public function beforeSave() {
		$conn = $this->getConnectionResolver()->connection();

		// Ignore NoSQL databases.
		if (!preg_match('/sql|postgres/', $conn->getDriverName())) {
			return;
		}

		$builder = $conn->getSchemaBuilder();
		$attributes = $this->attributes;

		//
		// TODO: Cache table structure for hasTable/hasColumn boosting
		//
		$table = $this->getTable();

		// Collection table doesn't exists yet: CREATE TABLE
		if (!$builder->hasTable($table)) {

			$builder->create($table, function($t) use ($attributes) {
				$t->increments('_id');
				foreach($attributes as $field => $value) {
					$t->{gettype($value)}($field)->nullable();
				}

				// Use timestamp instead of date for created_at/updated_at fields
				$t->integer('created_at');
				$t->integer('updated_at');
			});

		} else {

			// Add missing fields: ALTER TABLE.
			$builder->table($table, function($t) use ($attributes, $builder, $table) {
				foreach($attributes as $field => $value) {
					if (!$builder->hasColumn($table, $field) &&
							!$builder->hasColumn($table, "`{$field}`")) {
						$t->{gettype($value)}($field)->nullable();
					}
				}
			});

		}

	}

	public function drop() {
		$conn = $this->getConnectionResolver()->connection();
		$builder = $conn->getSchemaBuilder();
		$builder->dropIfExists($this->getTable());
		return true;
	}

}
