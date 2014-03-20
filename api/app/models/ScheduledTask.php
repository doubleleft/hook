<?php
namespace models;

class ScheduledTask extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public function app() {
		return $this->belongsTo('models\App');
	}

	/**
	 * Current app scope
	 * @example
	 *     ScheduledTask::current()->delete()
	 */
	public function scopeCurrent($query) {
		return $query->where('app_id', App::currentId());
	}

}



