<?php

namespace Client;

class Client {
	var $root_url = 'http://dl-api.dev/api/public/index.php/';

	public function get($segments) {
		return $this->parse(\Guzzle::get($this->root_url . $segments));
	}

	protected function parse($response) {
		return json_decode($response->getBody());
	}

}
