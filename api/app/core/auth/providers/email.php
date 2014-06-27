<?php
namespace Auth\Providers;

class Email extends Base {
	const TEMPLATE_FORGOT_PASSWORD = 'auth.forgot_password.html';

	/**
	 * Register a new user
	 */
	public function authenticate($data) {
		$this->validateParams($data);
		if ($existing = $this->findExistingUser($data)) {
			throw new \ForbiddenException(__CLASS__ . ': email already registered.');
		}
		$user = \Model\Auth::create($data);
		return $user->dataWithToken();
	}

	/**
	 * Verify if user already exists
	 */
	public function verify($data) {
		$this->validateParams($data);

		$userdata = null;
		if ($user = $this->findExistingUser($data)) {
			if ($user->password != $data['password']) {
				throw new \ForbiddenException(__CLASS__ . ": password invalid.");
			}
			$userdata = $user->dataWithToken();
		} else {
			if (!$user) {
				throw new \ForbiddenException(__CLASS__ . ": user not found.");
			}
		}
		return $userdata;
	}

	/**
	 * Trigger 'forgot password' email
	 */
	public function forgotPassword($data) {
		$user = $this->find('email', $data);

		if (!$user) {
			throw new \ForbiddenException(__CLASS__ . ": user not found.");
		}

		if (!isset($data['subject'])) {
			$data['subject'] = 'Forgot your password?';
		}

		$body_data = $user->generateForgotPasswordToken()->toArray();
		$body_data['token'] = $user->getAttribute(\Model\Auth::FORGOT_PASSWORD_FIELD);

		$template = isset($data['template']) ? $data['template'] : self::TEMPLATE_FORGOT_PASSWORD;

		return array(
			'success' => (\Mail::send(array(
				'subject' => $data['subject'],
				'from' => \Model\AppConfig::get('mail.from', 'no-reply@api.2l.cx'),
				'to' => $user->email,
				'body' => \Model\Module::template($template)->compile($body_data)
			)) === 1)
		);
	}

	/**
	 * Reset user password
	 */
	public function resetPassword($data) {
		if (!isset($data['token']) === 0) {
			throw new \Exception(__CLASS__ . ": you must provide a 'token'.");
		}
		if (!isset($data['password']) || strlen($data['password']) === 0) {
			throw new \Exception(__CLASS__ . ": you must provide a valid 'password'.");
		}

		$data[\Model\Auth::FORGOT_PASSWORD_FIELD] = $data['token'];
		$user = $this->find(\Model\Auth::FORGOT_PASSWORD_FIELD, $data);

		if ($user && $user->resetPassword($data['password'])) {
			return array('success' => true);
		} else {
			throw new \Exception(__CLASS__ . ": invalid or expired token.");
		}
	}

	public function findExistingUser($data) {
		$user = null;

		try {
			$user = $this->find('email', $data);
		} catch (\Illuminate\Database\QueryException $e) {}

		return $user;
	}

	protected function validateParams($data) {
		// validate email address
		if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new \Exception(__CLASS__ . ": you must provide a valid 'email'.");
		}

		// validate password
		if (!isset($data['password']) || strlen($data['password']) === 0) {
			throw new \Exception(__CLASS__ . ": you must provide a password.");
		}

	}

}

