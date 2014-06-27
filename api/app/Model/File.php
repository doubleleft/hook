<?php
namespace Model;

/**
 * File
 *
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
class File extends \Core\Model
{

	public static function boot() {
		static::creating(function($m) { $m->beforeCreate(); });
	}

	public static function base64($data) {
		if (is_string($data) && preg_match('/data:[a-z]+\/([a-z]+);base64,([^$]+)/', $data, $base64)) {
			return $base64;
		}
		return false;
	}

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function beforeCreate() {
		if ($this->file) {
			$provider = AppConfig::get('storage.provider', 'filesystem');

			if ($base64 = static::base64($this->file)){
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
