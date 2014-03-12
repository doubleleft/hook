<?php
namespace models;

class Collection extends \Core\Model
{
	protected $table = '_collections';

	protected $guarded = array();
	protected $primaryKey = '_id';

	protected static $observers;
	public static $lastTableName;

	public static function boot() {
		if (!static::$observers) { static::$observers = array(); }

		parent::boot();
		static::saving(function($model) { $model->beforeSave(); });
	}

	public static function loadObserver($table) {
		// Compile observer only if it isn't compiled yet.

		//
		// TODO: Clenaup previous observer to attach another.
		//
		// Since different collections share same class, loading
		// more than one observer will just register more events.
		//
		if (!isset(static::$observers[ $table ])) {
			if ($module = Module::observer($table)) {
				$observer = $module->compile();
				static::$observers[ $table ] = $observer;
				static::observe($observer);
			}
		}
	}

	/**
	 * from
	 * @param string $table table
	 * @return Illuminate\Database\Query\Builder
	 */
	public static function from($table) {
		static::$lastTableName = $table;
		static::loadObserver($table);
		return static::query()->from($table);
	}

	public function __construct(array $attributes = array()) {
		if (isset($attributes['table_name'])) {
			static::$lastTableName = $attributes['table_name'];
			$this->setTable(static::$lastTableName);
			unset($attributes['table_name']);
		} else if (static::$lastTableName) {
			$this->setTable(static::$lastTableName);
		}
		static::loadObserver($this->getTable());
		parent::__construct($attributes);
	}

	public function scopeFilter($query, $filters = null) {
		if ($filters) {
			foreach($filters as $where) {
				if (preg_match('/^[a-z_]+$/', $where[1]) !== 0 && strtolower($where[1]) !== 'like') {
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
	 * toArray. Modules may define a custom toArray method.
	 * @return array
	 */
	public function toArray() {
		$array = parent::toArray();
		$table = $this->getTable();

		if (isset(static::$observers[ $table ])) {
			$observer = static::$observers[ $table ];
			if (method_exists($observer, 'toArray')) {
				return $observer->toArray($this, $array);
			}
		}

		return $array;
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
					$datatype = strtolower(gettype($value));

					// Detect large text blocks to declare 'text' datatype.
					if ($datatype == 'string' && strlen($value) > 255) {
						$datatype = 'text';
					}

					if ($datatype !== 'array' && $datatype !== 'null') {
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
			$builder->table($table, function($t) use ($attributes, $builder, $table) {
				foreach($attributes as $field => $value) {
					if (!$builder->hasColumn($table, $field) &&
							!$builder->hasColumn($table, "`{$field}`")) {
						$datatype = strtolower(gettype($value));

						// Detect large text blocks to declare 'text' datatype.
						if ($datatype == 'string' && strlen($value) > 255) {
							$datatype = 'text';
						}

						if ($datatype !== 'array' && $datatype !== 'null') {
							$t->{$datatype}($field)->nullable();
						}
					}
				}
			});

		}
	}

}
