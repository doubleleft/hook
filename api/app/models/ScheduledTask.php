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

	public function toString() {
		$shortcuts = array(
			'hourly' => '* * * * *',
			'daily' => '* * * * *',
			'weekly' => '* * * * *',
			'monthly' => '* * * * *'
		);
		$schedule = preg_match('/[a-z]/', $this->schedule) ? $shortcuts[$this->schedule] : $this->schedule;
		$command = 'curl '; // TODO
		return $schedule . ' ' . $command;
	}

}



