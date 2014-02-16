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

		parent::beforeSave();
	}


}
