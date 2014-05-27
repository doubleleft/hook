<?php
namespace models;

class PushMessage extends DynamicModel
{
	protected $table = 'push_messages';

	public static function boot() {
		parent::boot();
		static::creating(function($model) { $model->beforeCreate(); });
	}

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function beforeCreate() {
		if (!AuthToken::current()) {
			// throw new \Exception("auth token is required to create push_messages.");
		}

		if (!$this->getAttribute('message')) {
			throw new \Exception("Can't create PushMessage: 'message' is required.");
		}

		$this->setAttribute('status', 0);
		$this->setAttribute('devices', 0);
        $this->setAttribute('devices_errors', 0);
		$this->beforeSave();
	}

}
