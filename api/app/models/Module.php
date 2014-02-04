<?php
namespace models;

class Module extends \Core\Model
{
	protected $guarded = array();
	protected $primaryKey = '_id';

	public function app() {
		return $this->belongsTo('models\App');
	}

}



