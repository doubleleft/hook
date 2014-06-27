<?php
namespace Model;

class RequestLog extends \Core\Model
{

	public function app() {
		return $this->belongsTo('models\App');
	}

}


