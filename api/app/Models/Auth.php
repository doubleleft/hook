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

	public function tokens() {
		return $this->hasMany('Models\AuthToken', 'auth_id');
	}

	public function generate_token() {
		return $this->tokens()->create(array(
			'app_id' => $this->app_id
		));
	}

}
