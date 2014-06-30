<?php
//
// Consider using ZeroMQ or Redis
// http://socketo.me/docs/push#tyingittogether
// http://blog.jmoz.co.uk/websockets-ratchet-react-redis/
//

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '';
$_SERVER['SERVER_NAME'] = 'websocket';
$_SERVER['SERVER_PORT'] = 80;

require __DIR__ . '/../app/bootstrap.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class PubSubServer implements WampServerInterface
{
    private $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    public function getHandler($conn)
    {
        $app = \Slim\Slim::getInstance();
        $credentials = $conn->WebSocket->request->getQuery()->toArray();

        //
        // Aparently, this doesn't work as expected.
        //
        // set x-auth-token
        if (isset($credentials['X-Auth-Token'])) {
            $app->request->headers->set('X-Auth-Token', $credentials['X-Auth-Token']);
            unset($credentials['X-Auth-Token']);
        }

        // remove "/" and possible "ws/" from resource path
        $resource = str_replace("ws/", "", substr($conn->WebSocket->request->getPath(), 1));
        $hash = md5($resource . join(",", array_values($credentials)));

        if (!isset($this->handlers[$hash])) {
            if ($key = models\AppKey::where('app_id', $credentials['X-App-Id'])
                ->where('key', $credentials['X-App-Key'])
                ->first()) {
                    $app->key = ((object) $key->toArray());

                    $channel = models\Module::channel($resource);
                    if ($channel) {
                        $this->handlers[$hash] = $channel->compile();
                    }
            }
        }

        var_dump((isset($this->handlers[$hash])));

        return (isset($this->handlers[$hash])) ? $this->handlers[$hash] : null;
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $handler = $this->getHandler($conn);
        if ($handler) {
            call_user_func_array(array($handler, 'onCall'), func_get_args());
        }
    }

    public function onPublish(ConnectionInterface $conn, $topic, $message, array $exclude, array $eligible)
    {
        $handler = $this->getHandler($conn);

        if ($handler) {
            call_user_func_array(array($handler, 'onPublish'), func_get_args());
        } else {

            // // Append auth_id if a logged user is the publisher
            // if ($token = models\AuthToken::current()) {
            // 	$message['auth_id'] = $token->auth_id;
            // }

            // By default exclude / eligible message to clients
            // --------------------------------------------
            foreach ($topic->getIterator() as $conn) {
                $is_excluded = !in_array($conn->WAMP->sessionId, $exclude);
                $is_eligible = count($eligible) === 0 || in_array($conn->WAMP->sessionId, $eligible);
                if ($is_excluded && $is_eligible) {
                    $conn->event($topic, $message);
                }
            }
        }
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $handler = $this->getHandler($conn);
        if ($handler) {
            call_user_func_array(array($handler, 'onSubscribe'), func_get_args());
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        $handler = $this->getHandler($conn);
        if ($handler) {
            call_user_func_array(array($handler, 'onUnSubscribe'), func_get_args());
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $handler = $this->getHandler($conn);

        if ($handler) {
            call_user_func_array(array($handler, 'onOpen'), func_get_args());
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $handler = $this->getHandler($conn);
        if ($handler) {
            call_user_func_array(array($handler, 'onClose'), func_get_args());
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $handler = $this->getHandler($conn);
        if ($handler) {
            call_user_func_array(array($handler, 'onError'), func_get_args());
        }
    }
}

class Channel
{
    private static $loop;
    public static function getLoop()
    {
        return static::$loop;
    }
    public static function setLoop(&$loop)
    {
        static::$loop = $loop;
    }
}

// Set up our WebSocket server for clients wanting real-time updates
$loop = React\EventLoop\Factory::create();
Channel::setLoop($loop);

$socket_server = new React\Socket\Server($loop);
$socket_server->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect

$io_server = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(new PubSubServer()
        )
    )
), $socket_server);

$loop->run();
