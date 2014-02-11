<?php
namespace Auth\Providers;

class Email extends Base {

	public function authenticate($data) {
		$user = $this->findExistingUser($data);
		if (!$user) {
			$user = \models\Auth::create($data);
		}
		return $user->dataWithToken();
	}

	public function verify($data) {
		$userdata = null;
		if ($user = $this->findExistingUser($data)) {
			$userdata = $user->dataWithToken();
		}
		return $userdata;
	}

	public function forgot_password($data) {
		$user = $this->findExistingUser($data);
		if (!$user) {
			throw new \ForbiddenException(__CLASS__ . ": user not found.");
		}
		// $sent = Mailer\Mail::send()
	}

	public function reset_password($data) {
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

