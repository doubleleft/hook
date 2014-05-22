<?php
namespace models;

/**
 * ChannelMessage
 *
 * @uses Collection
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
class ChannelMessage extends DynamicModel {
	const EVENT_CONNECT = 'connected';

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
		$auth = AuthToken::current();
		if ($auth) {
			$this->setAttribute('auth_id', $auth->auth_id);
		}

		parent::beforeSave();
	}


}
