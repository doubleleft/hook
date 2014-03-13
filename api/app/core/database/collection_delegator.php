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
	 * Handle custom query methods
	 *
	 * @param mixed $method method
	 * @param mixed $parameters parameters
	 * @return mixed
	 */
	public function __call($method, $parameters) {
		$caller = method_exists($this, $method) ? $this : $this->query;
		return call_user_func_array(array($caller, $method), $parameters);
	}

}
