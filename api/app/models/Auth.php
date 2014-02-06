<?php
namespace models;

class Auth extends Collection
{
	protected $guarded = array();
	protected $primaryKey = '_id';
	protected $table = 'auth';

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function tokens() {
		return $this->hasMany('models\AuthToken', 'auth_id');
	}

	public function generate_token() {
		return $this->tokens()->create(array(
			'app_id' => $this->app_id
		));
	}

	public function dataWithToken() {
		$data = $this->toArray();
		$data['token'] = $this->generate_token()->toArray();
		return $data;
	}


}
