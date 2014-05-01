<?php
// $_SERVER['REQUEST_METHOD'] = '';
// $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
// $_SERVER['REQUEST_URI'] = '';
// $_SERVER['SERVER_NAME'] = 'localhost';
// $_SERVER['SERVER_PORT'] = '80';
// require __DIR__ . "/../index.php";

require __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnit_Framework_TestCase {
	protected $base_url = 'http://dl-api.dev/api/index.php/';

	public function setUp() {
		$this->useApp('default');
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function useApp($id, $db_driver = 'sqlite') {
		$apps = $this->get('apps/list');
		return $apps[0];
	}

	public function get($uri, $headers = array()) {
		return $this->request('get', $uri, $headers);
	}

	public function post($uri, $data = array(), $headers = array()) {
		return $this->request('post', $uri, $data, $headers);
	}

	public function put($uri, $data = array(), $headers = array()) {
		return $this->request('put', $uri, $data, $headers);
	}

	public function delete($uri, $headers = array()) {
		return $this->request('delete', $uri, $data, $headers);
	}

	protected function request($method, $uri, $data = array(), $headers = array()) {
		$client = new \Guzzle\Http\Client();
		return $client->{$method}($this->base_url . $uri, $headers, $data)->send()->json();
	}

}
