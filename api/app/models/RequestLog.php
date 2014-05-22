<?php
namespace models;

class RequestLog extends \Core\Model
{

	public function app() {
		return $this->belongsTo('models\App');
	}

}


