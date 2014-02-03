<?php
namespace Auth\Providers;

class Email extends Base {

	public function register($data) {
		if (!isset($data['email'])) {
			throw new \Exception("Auth\Providers\Email: 'email' field is required.");
		}
		return $this->findOrRegister('email', $data);
	}

}

