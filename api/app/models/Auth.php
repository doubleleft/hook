<?php
namespace models;

class Auth extends Collection
{
	const FORGOT_PASSWORD_FIELD = 'forgot_password';

	protected $guarded = array();
	protected $primaryKey = '_id';
	protected $table = 'auth';

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function tokens() {
		return $this->hasMany('models\AuthToken', 'auth_id');
	}

	public function generateToken() {
		return $this->tokens()->create(array(
			'app_id' => $this->app_id
		));
	}

	public function forgotPassword() {
	}

	public function toArray() {
		$arr = parent::toArray();

		/**
		 * FIXME: find other way to hide password / tokens from authentication
		 */
		if (isset($arr['password'])) { unset($arr['password']); }
		if (isset($arr[self::FORGOT_PASSWORD_FIELD])) { unset($arr[self::FORGOT_PASSWORD_FIELD]); }

		return $arr;
	}

	public function dataWithToken() {
		$data = $this->toArray();
		$data['token'] = $this->generateToken()->toArray();
		return $data;
	}

}
