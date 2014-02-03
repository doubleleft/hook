<?php
namespace Models;

class Auth extends Collection
{
	protected $guarded = array();
	protected $primaryKey = '_id';
	protected $table = 'auth';

	public function app() {
		return $this->belongsTo('Models\App');
	}

}
