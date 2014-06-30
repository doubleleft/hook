<?php
// $_SERVER['REQUEST_METHOD'] = '';
// $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
// $_SERVER['REQUEST_URI'] = '';
// $_SERVER['SERVER_NAME'] = 'localhost';
// $_SERVER['SERVER_PORT'] = '80';
// require __DIR__ . "/../index.php";

require __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnit_Framework_TestCase
{
    // protected $base_url = 'http://localhost/api/index.php/';
    // protected $base_url = 'http://localhost/api/index.php/';
    protected $base_url = 'http://dl-api.dev/api/index.php/';
    protected $app;

    public function setUp()
    {
        $this->app = $this->useApp('default');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function useApp($id, $db_driver = 'sqlite')
    {
        $apps = $this->get('apps/list');
        if (!isset($apps[0])) {
            $this->post('apps', array(
                'app' => array('name' => 'phpunit')
            ));

            return $this->useApp($id, $db_driver);
        }

        return $apps[0]['keys'][0];
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
            'X-App-Id' => $this->app['app_id'],
            'X-App-Key' => $this->app['key']
        ));

        return $client->{$method}($uri, $headers, json_encode($data), array(
            'exceptions' => false
        ))->send()->json();
    }

}
