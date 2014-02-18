<?php

namespace models;

class ChannelMessage extends Collection {
	const EVENT_CONNECT = 'connected';

	protected $guarded = array();
	protected $primaryKey = '_id';
	protected $table = 'channel_messages';

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function beforeSave() {
		// Check if a CONNECT message is being created, to
		// generate a unique client_id.
		if (!$this->_id && $this->getAttribute('event') && $this->event == self::EVENT_CONNECT) {
			$this->setAttribute('client_id', uniqid());
		}

		// Fill auth_id on message if there is a user authenticated.
		$app = \Slim\Slim::getInstance();
		if ($app->auth_token) {
			$this->setAttribute('auth_id', $app->auth_token->auth_id);
		}

		parent::beforeSave();
	}


}
