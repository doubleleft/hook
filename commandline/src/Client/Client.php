<?php

namespace Client;

class Client {
	// public static $endpoint = 'http://dl-api.ddll.co/';
	// public static $endpoint = 'http://api.2d.cx';
	public static $endpoint = 'http://dl-api.dev/api/index.php/';
	public static $debug = false;

	public static function setEndpoint($endpoint) {
		static::$endpoint = $endpoint;
	}

	public static function getEndpoint() {
		return static::$endpoint;
	}

	public static function setDebug($debug) {
		static::$debug = $debug;
	}

	public function get($segments) {
		return $this->parse($this->request('get', $segments));
	}

	public function delete($segments) {
		return $this->parse($this->request('delete', $segments));
	}

	public function post($segments, $data = array()) {
		return $this->parse($this->request('post', $segments, $data));
	}

	protected function parse($response) {
		$data = json_decode($response->getBody());

		if (isset($data->error)) {
			// TODO: create Output class for coloring features
			$url = parse_url(self::$endpoint);
			$message = "\033[1;31m"; // red
			$message .= "ERROR {$url['host']}: '" . $data->error . "'" ;
			$message .= "\033[0;39m"; // clear color
			$message .= PHP_EOL;
			die($message);
		}

		return $data;
	}

	protected function request($method, $segments, $data = array()) {
		$client = new \Guzzle\Http\Client(self::$endpoint);
		$request = $client->{$method}($segments, $this->getHeaders(), json_encode($data), array(
			'debug' => static::$debug,
			'exceptions' => false
		));
		return $request->send();
	}

	protected function getHeaders() {
		$config = Project::getConfig();
		$headers = array(
			'Content-Type' => 'application/json',
			'X-Public-Key' => file_get_contents($_SERVER['HOME'] . '/.ssh/id_rsa.pub')
		);
		if (!empty($config)) {
			$headers['X-App-Id'] = $config['app_id'];
			$headers['X-App-Key'] = $config['key'];
		}
		return $headers;
	}

}
