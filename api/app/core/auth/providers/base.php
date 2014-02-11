<?php
namespace Auth\Providers;

class Base {

	public function authenticate($data) {
		throw new \Exception("'authenticate' not implemented on this provider.");
	}

	public function verify($data) {
		throw new \Exception("'verify' not implemented on this provider.");
	}

	public function forgot_password($data) {
		throw new \Exception("'forgot_password' not implemented on this provider.");
	}

	protected function find($key_field, $data) {
		return \models\Auth::where($key_field, $data[$key_field])
			->where('app_id', $data['app_id'])
			->first();
	}

}
