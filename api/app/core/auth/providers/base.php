<?php
namespace Auth\Providers;

class Base {

	public function authenticate($data) {
		throw new \Exception("'register' not implemented on this provider.");
	}

	protected function find($key_field, $data) {
		return \models\Auth::where($key_field, $data[$key_field])
			->where('app_id', $data['app_id'])
			->first();
	}

}
