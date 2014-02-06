<?php

namespace Client;

class Client {
	// var $root_url = 'http://dl-api.ddll.co/';
	// var $root_url = 'http://api.2d.cx';
	var $root_url = 'http://dl-api.dev/api/index.php/';

	public function get($segments) {
		return $this->parse(\Guzzle::get($this->root_url . $segments));
	}

	public function delete($segments) {
		return $this->parse(\Guzzle::delete($this->root_url . $segments));
	}

	public function post($segments, $data = array()) {
		return $this->parse(\Guzzle::post($this->root_url . $segments, array(
			'body' => json_encode($data)
		)));
	}

	protected function parse($response) {
		$data = json_decode($response->getBody());

		if (isset($data->error)) {
			$url = parse_url($this->root_url);
			die("{$url['host']} responded with error: '" . $data->error . "'" . PHP_EOL);
		}

		return $data;
	}

}
