<?php
namespace Auth\Providers;

class Email extends Base {

	/**
	 * Register a new user
	 */
	public function authenticate($data) {
		$user = $this->findExistingUser($data);
		if (!$user) {
			$user = \models\Auth::create($data);
		}
		return $user->dataWithToken();
	}

	/**
	 * Verify if user already exists
	 */
	public function verify($data) {
		$userdata = null;
		if ($user = $this->findExistingUser($data)) {
			$userdata = $user->dataWithToken();
		}
		return $userdata;
	}

	/**
	 * Trigger forgot password email
	 */
	public function forgotPassword($data) {
		$user = $this->findExistingUser($data);
		if (!$user) {
			throw new \ForbiddenException(__CLASS__ . ": user not found.");
		}

		$username = (isset($user->name)) ? $user->name : $user->email;
		$success = Mail::send(array(
			'to' => "{$username} <{$user->email}>",
			'from' => models\AppConfig::get('mail.from', 'no-reply@api.2l.cx'),
			'body' => models\Module::template('auth.forgot_password.html')->compile($user),
		));
	}

	/**
	 * Reset user password
	 */
	public function resetPassword($data) {
		if ($user = $this->findExistingUser($data)) {

		}
	}

	protected function findExistingUser($data) {

		// validate email address
		if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new \Exception(__CLASS__ . ": you must provide a valid 'email'.");
		}

		// validate password
		if (!isset($data['password']) || strlen($data['password']) === 0) {
			throw new \Exception(__CLASS__ . ": you must provide a password.");
		}

		$user = null;

		try {
			$user = $this->find('email', $data);
			if ($user && $user->password != $data['password']) {
				throw new \ForbiddenException(__CLASS__ . ": password invalid.");
			}
		} catch (\Illuminate\Database\QueryException $e) {}

		return $user;
	}

}

