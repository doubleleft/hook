<?php
namespace Auth\Providers;

class Email extends Base {

	public function authenticate($data) {
		$user = null;

		// validate email address
		if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new \Exception(__CLASS__ . ": you must provide a valid 'email'.");
		}

		// validate password
		if (!isset($data['password']) || strlen($data['password']) === 0) {
			throw new \Exception(__CLASS__ . ": you must provide a password.");
		}

		try {
			$user = $this->find('email', $data);
			if ($user && $user->password != $data['password']) {
				throw new \ForbiddenException(__CLASS__ . ": password invalid.");
			}
		} catch (\Illuminate\Database\QueryException $e) {}

		if (!$user) {
			$user = \models\Auth::create($data);
		}

		return $user->dataWithToken();
	}

}

