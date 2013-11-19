<?php
namespace Models;

class RequestLog extends \Core\Model
{
	protected $guarded = array();

	public function app() {
		return $this->belongsTo('Models\App');
	}

}


