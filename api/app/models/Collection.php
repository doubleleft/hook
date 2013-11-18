<?php
namespace Models;

class Collection extends \Core\Model
{
	protected $guarded = array();

	public function application() {
		return $this->belongsTo('Models\App');
	}

}


