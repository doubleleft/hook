<?php
namespace Auth\Providers;

class Base {

	public function authenticate($data) {
		throw new \Exception("'authenticate' not implemented on this provider.");
	}

	public function verify($data) {
		throw new \Exception("'verify' not implemented on this provider.");
	}

	public function forgotPassword($data) {
		throw new \Exception("'forgotPassword' not implemented on this provider.");
	}

	public function resetPassword($data) {
		throw new \Exception("'resetPassword' not implemented on this provider.");
	}

	protected function find($key_field, $data) {
		return \Model\Auth::where($key_field, $data[$key_field])
			->where('app_id', $data['app_id'])
			->first();
	}

}
