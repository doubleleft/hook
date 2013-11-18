<?php
namespace Models;

class Module extends \Core\Model
{
	protected $guarded = array();

	public function application() {
		return $this->belongsTo('Models\App');
	}

}



