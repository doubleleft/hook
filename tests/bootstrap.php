<?php

// setup dummy server variables
$_SERVER['REQUEST_METHOD'] = '';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Hook.php';

// Force some application key for testing
Hook\Database\AppContext::setKey(Hook\Model\AppKey::with('app')->first());

$app = \Slim\Slim::getInstance();
$app->log->setWriter(new Hook\Logger\LogWriter(storage_dir() . '/logs.txt'));

class TestCase extends PHPUnit_Framework_TestCase
{
}

class HTTP_TestCase extends PHPUnit_Framework_TestCase
{
    // protected $base_url = 'http://localhost/index.php/';
    // protected $base_url = 'http://localhost/index.php/';
    protected $base_url = 'http://dl-api.dev:58054/index.php/';
    protected $app_keys = array();
    protected $app_key = array();
    // protected $base_url = 'http://dl-api.dev/index.php/';

    public function setUp()
    {
        $this->useApp('default');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function useApp($id)
    {
        $db_driver = getenv('DB_DRIVER') ?: 'mysql';

        $apps = $this->get('apps');
        if (!isset($apps[0])) {
            $this->post('apps', array(
                'app' => array('name' => 'phpunit')
            ));
            return $this->useApp($id);
        }

        // associate keys by type
        foreach($apps[0]['keys'] as $key) {
            if ($key['deleted_at']==null) {
                $this->app_keys[$key['type']] = $key;
            }
        }

        // use browser key by default
        $this->setKeyType('browser');
    }

    public function setKeyType($type)
    {
        $this->app_key = $this->app_keys[$type];
    }

    public function get($uri, $headers = array())
    {
        return $this->request('get', $uri, array(), $headers);
    }

    public function post($uri, $data = array(), $headers = array())
    {
        return $this->request('post', $uri, $data, $headers);
    }

    public function put($uri, $data = array(), $headers = array())
    {
        return $this->request('put', $uri, $data, $headers);
    }

    public function delete($uri, $data = array(), $headers = array())
    {
        return $this->request('delete', $uri, $data, $headers);
    }

    protected function request($method, $uri, $data = array(), $headers = array())
    {
        $uri = $this->base_url . $uri;
        $client = new \Guzzle\Http\Client();

        // $uri .= '?X-App-Id=' . $this->app['app_id'] . '&X-App-Key=' . $this->app['key'];

        $headers = array_merge($headers, array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-App-Id' => ($this->app_key) ? $this->app_key['app_id'] : null,
            'X-App-Key' => ($this->app_key) ? $this->app_key['key'] : null,
            'User-Agent' => 'hook-cli'
        ));

        return $client->{$method}($uri, $headers, json_encode($data), array(
            'exceptions' => false
        ))->send()->json();
    }

}
