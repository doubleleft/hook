<?php
namespace Models;

class Collection extends \Jenssegers\Mongodb\Model
{
	protected $guarded = array();

	public function application() {
		return $this->belongsTo('Models\App');
	}

}


