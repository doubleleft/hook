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

		// TODO: ALTER TABLE when necessary.
		if (!$builder->hasTable($this->getTable())) {
			$attributes = $this->attributes;

			// Create table with requested fields.
			$builder->create($this->getTable(), function($t) use ($attributes) {
				$t->increments('_id');
				foreach($attributes as $field => $value) {
					$t->{gettype($value)}($field);
				}
				$t->timestamps();
			});
		}

	}

}
