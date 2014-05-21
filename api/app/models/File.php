<?php
namespace models;

/**
 * File
 *
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
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
			$provider = AppConfig::get('storage.provider', 'filesystem');

			if (is_string($this->file) && preg_match('/^data:[a-z]+\/([a-z]+);base64,([^$]+)/', $this->file, $base64)){
				$this->name = "base64" . uniqid() . '.' . $base64[1];
				$this->path = \Storage\Provider::get($provider)->store($this->name, base64_decode($base64[2]));
				$this->mime = $base64[1];

			} else {
				$this->name = $this->file['name'];
				$this->mime = $this->file['type'];
				$this->path = \Storage\Provider::get($provider)->upload($this->file);
			}
			unset($this->attributes['file']);
		}
	}

}
