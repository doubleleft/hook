<?php
namespace Models;

class Collection extends \Core\Model
{
	protected $guarded = array();

	public function __construct(array $attributes = array()) {
		if (isset($attributes['table_name'])) {
			$this->setTable($attributes['table_name']);
			unset($attributes['table_name']);
		}
		parent::__construct($attributes);
	}

	public function application() {
		return $this->belongsTo('Models\App');
	}

}


