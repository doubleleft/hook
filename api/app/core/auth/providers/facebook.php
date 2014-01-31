<?php

namespace Auth\Providers;

class Facebook {

	public function getUserData($accessToken) {
		$client = new \Guzzle\Http\Client("https://graph.facebook.com");
		$response = $client->get("/me?access_token={$accessToken}")->send();
		$data = json_decode($response->getBody(), true);

		if (isset($data['education'])) { $data['education'] = $data['education'][0]['type']; }
		if (isset($data['languages'])) { $data['languages'] = $data['languages'][0]['name']; }
		unset($data['work']);

		return $data;
	}

}
