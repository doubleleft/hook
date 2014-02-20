<?php

namespace Client;

class Client {
	// public static $endpoint = 'http://dl-api.ddll.co/';
	// public static $endpoint = 'http://api.2d.cx';
	public static $endpoint = 'http://dl-api.dev/api/index.php/';

	public static function setEndpoint($endpoint) {
		static::$endpoint = $endpoint;
	}

	public static function getEndpoint() {
		return static::$endpoint;
	}

	public function get($segments) {
		return $this->parse(\Guzzle::get(self::$endpoint . $segments, array(
			'headers' => $this->getHeaders()
		)));
	}

	public function delete($segments) {
		return $this->parse(\Guzzle::delete(self::$endpoint . $segments, array(
			'headers' => $this->getHeaders()
		)));
	}

	public function post($segments, $data = array()) {
		return $this->parse(\Guzzle::post(self::$endpoint . $segments, array(
			'headers' => $this->getHeaders(),
			'body' => $data
		)));
	}

	protected function parse($response) {
		$data = json_decode($response->getBody());

		if (isset($data->error)) {
			$url = parse_url(self::$endpoint);
			die("{$url['host']} responded with error: '" . $data->error . "'" . PHP_EOL);
		}

		return $data;
	}

	protected function getHeaders() {
		$config = Project::getConfig();
		$headers['X-App-Id'] = $config['app_id'];
		$headers['X-App-Key'] = $config['key'];
		// $headers['X-Public-Key'] = file_get_contents($_SERVER['HOME'] . '/.ssh/id_rsa.pub');
		return $headers;
	}

}
