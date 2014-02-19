<?php
namespace models;

class Collection extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		parent::boot();
		static::saving(function($model) { $model->beforeSave(); });
	}

	public function __construct(array $attributes = array()) {
		if (isset($attributes['table_name'])) {
			$this->setTable($attributes['table_name']);
			unset($attributes['table_name']);
		}
		parent::__construct($attributes);
	}

	public function scopeFilter($query, $filters = null) {
		if ($filters) {
			foreach($filters as $where) {
				if (preg_match('/^[a-z_]+$/', $where[1]) !== 0) {
					$method = 'where' . ucfirst(\Illuminate\Support\Str::camel($where[1]));
					$query->{$method}($where[0], $where[2]);
				} else {
					$query->where($where[0], $where[1], $where[2]);
				}
			}
		}
		return $query;
	}

	public function app() {
		return $this->belongsTo('models\App');
	}

	/**
	 * Drop the collection
	 * @method drop
	 */
	public function drop() {
		$conn = $this->getConnectionResolver()->connection();
		$builder = $conn->getSchemaBuilder();
		$builder->dropIfExists($this->getTable());
		return true;
	}

	//
	// Hooks
	//

	public function beforeSave() {
		$connection = $this->getConnectionResolver()->connection();

		// Try to migrate schema.
		// Ignore NoSQL databases.
		if (!$connection->getPdo()) { return; }

		$builder = $connection->getSchemaBuilder();
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
					$datatype = gettype($value);
					if ($datatype !== 'array') {
						$t->{gettype($value)}($field)->nullable();
					}
				}

				// Use timestamp instead of date for created_at/updated_at fields
				$t->integer('created_at');
				$t->integer('updated_at');
			});

		} else {

			// Add missing fields: ALTER TABLE.
			// TODO: DRY
			$builder->table($table, function($t) use ($attributes, $builder, $table) {
				foreach($attributes as $field => $value) {
					if (!$builder->hasColumn($table, $field) &&
							!$builder->hasColumn($table, "`{$field}`")) {
						$datatype = gettype($value);
						if ($datatype !== 'array') {
							$t->{gettype($value)}($field)->nullable();
						}
					}
				}
			});

		}
	}

}
