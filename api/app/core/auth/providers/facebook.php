<?php
namespace Auth\Providers;

class Facebook extends Base {

	public function register($data) {
		$client = new \Guzzle\Http\Client("https://graph.facebook.com");
		$response = $client->get("/me?access_token={$data['accessToken']}")->send();
		$data = json_decode($response->getBody(), true);

		// rename 'facebook_id' field
		$data['facebook_id'] = $data['id'];
		unset($data['id']);

		if (isset($data['education'])) { $data['education'] = $data['education'][0]['type']; }

		$unusual_fields = array('work', 'languages', 'hometown', 'location', 'sports', 'favorite_teams', 'favorite_athletes');
		foreach($unusual_fields as $field) {
			unset($data[$field]);

			// if (isset($data[$field])) {
			// 	$data[$field] = $data[$field][0]['name'];
			// }
		}

		return $this->findOrRegister('facebook_id', $data);
	}

}
