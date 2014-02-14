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

	public function keys() {
		return $this->hasMany('models\AppKey', 'app_id');
	}

	public function modules() {
		return $this->hasMany('models\Module', 'app_id');
	}

	public function configs() {
		return $this->hasMany('models\AppConfig', 'app_id');
	}

	public function generate_key() {
		return $this->keys()->create(array());
	}

	public function afterCreate() {
		$this->generate_key();
	}

	public function toArray() {
		$arr = parent::toArray();
		$arr['keys'] = $this->keys->toArray();
		return $arr;
	}

	public function storage() {
		return new Core\Storage\File($this);
	}

	/**
	 * Current app scope
	 * @example
	 *     App::current()->where('name', 'like', 'mail.%')->get()
	 */
	public function scopeCurrent($query) {
		$app = \Slim\Slim::getInstance();
		return $query->where('_id', $app->key->app_id);
	}

}

