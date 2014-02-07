<?php
namespace Auth\Providers;

class Facebook extends Base {

	public function authenticate($data) {
		$data = $this->requestFacebookGraph($data);

		// don't fill unusual fields
		$unusual_fields = array('work', 'languages', 'hometown', 'location', 'sports', 'favorite_teams', 'favorite_athletes', 'education');
		foreach($unusual_fields as $field) {
			unset($data[$field]);
		}

		$user = null;
		try {
			$user = $this->find('facebook_id', $data);
		} catch (\Illuminate\Database\QueryException $e) {}

		if (!$user) {
			$user = \models\Auth::create($data);
		}

		return $user->dataWithToken();
	}

	public function check($data) {
		$userdata = null;
		if ($user = $this->find('facebook_id', $this->requestFacebookGraph($data))) {
			$userdata = $user->dataWithToken();
		}
		return $userdata;
	}

	protected function requestFacebookGraph($data) {
		// validate accessToken
		if (!isset($data['accessToken'])) {
			throw new \Exception(__CLASS__ . ": you must provide user 'accessToken'.");
		}

		$app_id = $data['app_id'];

		$client = new \Guzzle\Http\Client("https://graph.facebook.com");
		$response = $client->get("/me?access_token={$data['accessToken']}")->send();
		$data = json_decode($response->getBody(), true);

		// rename 'facebook_id' field
		$data['app_id'] = $app_id;
		$data['facebook_id'] = $data['id'];
		unset($data['id']);

		return $data;
	}

}
