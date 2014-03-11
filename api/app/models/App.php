<?php
namespace models;

class App extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		parent::boot();
		static::created(function($instance) { $instance->afterCreate(); });
	}

	/**
	 * currentId
	 * @static
	 * @return int
	 */
	public static function currentId() {
		$app = \Slim\Slim::getInstance();
		return $app->key->app_id;
	}

	/**
	 * collection
	 * @static
	 * @param mixed $name name
	 * @return models\Collection
	 */
	public static function collection($name) {
		return Collection::query()->from($name)->where('app_id', static::currentId());
	}

	public function keys() {
		return $this->hasMany('models\AppKey', 'app_id');
	}

	public function modules() {
		return $this->hasMany('models\Module', 'app_id');
	}

	public function configs() {
		return $this->hasMany('models\AppConfig', 'app_id');
	}

	public function generate_key($admin=false) {
		return $this->keys()->create(array('admin' => $admin));
	}

	public function afterCreate() {
		// Generate admin key
		$this->generate_key(true);

		// Generate user key
		$this->generate_key();
	}

	/**
	 * current
	 *
	 * @example
	 *     App::current()->where('name', 'like', 'mail.%')->get()
	 *
	 * @static
	 * @return models\App
	 */
	public function scopeCurrent($query) {
		return $query->where('_id', static::currentId());
	}

	public function toArray() {
		$arr = parent::toArray();
		$arr['keys'] = $this->keys->toArray();
		return $arr;
	}

}

