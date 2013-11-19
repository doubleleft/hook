<?php
namespace Models;

class Module extends \Core\Model
{
	protected $guarded = array();

	public function app() {
		return $this->belongsTo('Models\App');
	}

}



