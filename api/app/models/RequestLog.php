<?php
namespace Models;

class RequestLog extends \Jenssegers\Mongodb\Model
{
	protected $guarded = array();

	public function application() {
		return $this->belongsTo('Models\App');
	}

}


