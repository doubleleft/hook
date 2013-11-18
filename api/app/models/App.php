<?php
namespace Models;

class App extends \Jenssegers\Mongodb\Model
{
	protected $guarded = array();

	public static function boot() {
		parent::boot();
		static::saved(function($instance) { $instance->afterSave(); });
	}

	public function keys() {
		return $this->hasMany('Models\AppKey', 'app_id');
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

}

