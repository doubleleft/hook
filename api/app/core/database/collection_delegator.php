<?php
namespace core;

/**
 * CollectionDelegator
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class CollectionDelegator {

	/**
	 * name
	 * @var string
	 */
	protected $name;

	/**
	 * query
	 * @var Illuminate\Database\Query\Builder
	 */
	protected $query;

	/**
	 * Create a new CollectionDelegator instance.
	 *
	 * @param string $name name
	 * @param Illuminate\Database\Query\Builder $query query
	 */
	public function __construct($name, $app_id, $query) {
		$this->name = $name;
		$this->app_id = $app_id;
		$this->query = $query->where('app_id', $app_id);
	}

	/**
	 * Create a new Collection instance. No database operations here.
	 *
	 * @param array $attributes attributes
	 * @return \models\Collection
	 */
	public function create_new(array $attributes) {
		return new \models\Collection(array_merge($attributes, array(
			'table_name' => $this->name,
			'app_id' => $this->app_id
		)));
	}

	/**
	 * Create a new record into the database.
	 *
	 * @param array $attributes attributes
	 * @return \models\Collection
	 */
	public function create(array $attributes) {
		$model = $this->create_new($attributes);
		$model->save();
		return $model;
	}

	/**
	 * filter
	 *
	 * @param array $filters filters
	 * @return \core\CollectionDelegator
	 */
	public function filter($filters = null) {
		if ($filters) {
			foreach($filters as $where) {
				if (preg_match('/^[a-z_]+$/', $where[1]) !== 0 && strtolower($where[1]) !== 'like') {
					$method = 'where' . ucfirst(\Illuminate\Support\Str::camel($where[1]));
					$this->query->{$method}($where[0], $where[2]);
				} else {
					$this->query->where($where[0], $where[1], $where[2]);
				}
			}
		}
		return $this;
	}

	/**
	 * Add an "order by" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string|int  $direction
	 * @return \core\CollectionDelegator
	 */
	public function sort($column, $direction = 'asc') {
		if (is_int($direction)) {
			$direction = ($direction == -1) ? 'desc' : 'asc';
		}
		$this->query->orderBy($column, $direction);
		return $this;
	}

	/**
	 * Shortcut for get+toArray methods.
	 * @param string $columns columns
	 * @return array
	 */
	public function toArray($columns=array('*')) {
		return $this->query->get($columns)->toArray();
	}

	/**
	 * Shortcut for get+toJson methods.
	 * @param string $columns columns
	 * @return string
	 */
	public function toJson($columns=array('*')) {
		return $this->query->get($columns)->toJson();
	}

	/**
	 * Handle Illuminate\Database\Query\Builder methods.
	 *
	 * @param mixed $method method
	 * @param mixed $parameters parameters
	 * @return \core\CollectionDelegator
	 */
	public function __call($method, $parameters) {
		$mixed = call_user_func_array(array($this->query, $method), $parameters);
		if ($mixed instanceof \Illuminate\Database\Eloquent\Builder || $mixed instanceof \Illuminate\Database\Query\Builder ) {
			return $this;
		} else {
			return $mixed;
		}
	}

}
