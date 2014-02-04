<?php
namespace Auth\Providers;

class Base {

	public function register($data) {
		throw new \Exception("'register' not implemented on this provider.");
	}

	protected function findOrRegister($key_field, $data) {
		$data['app_id'] = \Slim\Slim::getInstance()->key->app_id;
		$user = null;

		try {
			$user = \models\Auth::where($key_field, $data[$key_field])
				->where('app_id', $data['app_id'])
				->first();
		} catch (\Illuminate\Database\QueryException $e) {
		}

		if (!$user) {
			$user = \models\Auth::create($data);
		}

		$data = $user->toArray();
		$data['token'] = $user->generate_token()->toArray();

		return $data;
	}

}
