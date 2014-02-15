<?php
namespace models;

class Auth extends Collection
{
	const FORGOT_PASSWORD_FIELD = 'forgot_password_token';
	const FORGOT_PASSWORD_EXPIRATION_FIELD = 'forgot_password_expiration';
	const FORGOT_PASSWORD_EXPIRATION_TIME = 14400; // (60 * 60 * 4) = 4 hours

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

	public function generateForgotPasswordToken() {
		$this->setAttribute(self::FORGOT_PASSWORD_FIELD, md5(uniqid(rand(), true)));
		$this->setAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD, time() + self::FORGOT_PASSWORD_EXPIRATION_TIME);
		$this->save();
		return $this;
	}

	/**
	 * Reset user password
	 */
	public function resetPassword($newPassword) {
		$success = false;
		if (!$this->isForgotPasswordTokenExpired()) {
			$this->password = $newPassword;
			$this->setAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD, time()); // expire token
			$success = $this->save();
		}
		return $success;
	}

	protected function isForgotPasswordTokenExpired() {
		return time() > $this->getAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD);
	}

	public function toArray() {
		$arr = parent::toArray();

		/**
		 * FIXME: find other way to hide password / tokens from authentication
		 */
		if (isset($arr['password'])) { unset($arr['password']); }
		if (isset($arr[self::FORGOT_PASSWORD_FIELD])) { unset($arr[self::FORGOT_PASSWORD_FIELD]); }
		if (isset($arr[self::FORGOT_PASSWORD_EXPIRATION_FIELD])) { unset($arr[self::FORGOT_PASSWORD_EXPIRATION_FIELD]); }

		return $arr;
	}

	public function dataWithToken() {
		$data = $this->toArray();
		$data['token'] = $this->generateToken()->toArray();
		return $data;
	}

}
