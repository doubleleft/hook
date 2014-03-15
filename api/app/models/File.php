<?php
namespace models;

class File extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		static::creating(function($m) { $m->beforeCreate(); });
	}

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function beforeCreate() {
		if ($this->file) {
			$this->name = $this->file['name'];
			$this->mime = $this->file['type'];

			$provider = AppConfig::get('storage.provider', 'filesystem');
			$this->path = \Storage\Provider::get($provider)->upload($this->file);
			unset($this->attributes['file']);
		}
	}

}
