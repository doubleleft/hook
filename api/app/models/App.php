<?php
namespace Models;

class App extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		parent::boot();
		static::saved(function($instance) { $instance->afterSave(); });
	}

	public function keys() {
		return $this->hasMany('Models\AppKey', 'app_id');
	}

	public function modules() {
		return $this->hasMany('Models\Module', 'app_id');
	}

	public function afterSave()
	{
		$this->keys()->create(array());
	}

	public function toArray() {
		$arr = parent::toArray();
		$arr['keys'] = $this->keys->toArray();
		return $arr;
	}

	public function storage() {
		return new Core\Storage\File($this);
	}

}

