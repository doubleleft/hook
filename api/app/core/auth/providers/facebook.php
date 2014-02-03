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
		if (isset($data['languages'])) { $data['languages'] = $data['languages'][0]['name']; }
		unset($data['work']);

		return $this->findOrRegister('facebook_id', $data);
	}

}
