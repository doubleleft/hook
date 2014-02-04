<?php
namespace Models;

class File extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public static function boot() {
		static::creating(function($m) { $m->beforeCreate(); });
	}

	public function app() {
		return $this->belongsTo('Models\App');
	}

	public function beforeCreate() {
		if ($this->attributes['file']) {
			unset($this->attributes['file']);

			$this->name = $this->file['name'];
			$this->mime = $this->file['mime-type'];
			$this->path = $this->app->storage()->upload($this->file);

		}
	}

}




