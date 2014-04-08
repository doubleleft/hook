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

            if(is_string($this->file) && strpos($this->file, "data:image/png") !== false){
               $this->path = \Storage\Provider::get($provider)->decodeBase64($this->file);
               $this->name = "base_64".uniqid();
               $this->mime = "image/png";

            }else{
                $this->name = $this->file['name'];
                $this->mime = $this->file['type'];
                $this->path = \Storage\Provider::get($provider)->upload($this->file);
            }
			unset($this->attributes['file']);
		}
	}

}
