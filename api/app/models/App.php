<?php
namespace models;

class App extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		parent::boot();
		static::creating(function($instance) { $instance->beforeCreate(); });
		// static::saved(function($instance) { $instance->afterSave(); });
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

	public function beforeCreate() {
		$this->generate_key();
	}

	// public function afterSave()
	// {
	// }

	public function toArray() {
		$arr = parent::toArray();
		$arr['keys'] = $this->keys->toArray();
		return $arr;
	}

	public function storage() {
		return new Core\Storage\File($this);
	}

}

