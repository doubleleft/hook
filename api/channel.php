<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '';
$_SERVER['SERVER_NAME'] = 'websocket';
$_SERVER['SERVER_PORT'] = 80;

require __DIR__ . '/app/bootstrap.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Channel implements WampServerInterface {

	public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
	}

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
		echo "publish..." . PHP_EOL;
		var_dump($topic);
		var_dump($event);
		$topic->broadcast($event);
	}

	public function onSubscribe(ConnectionInterface $conn, $topic) { }
	public function onUnSubscribe(ConnectionInterface $conn, $topic) { }

	public function onOpen(ConnectionInterface $conn) {
		echo "openned..." . PHP_EOL;
 	}
	public function onClose(ConnectionInterface $conn) {
		echo "closed..." . PHP_EOL;
 	}
	public function onError(ConnectionInterface $conn, \Exception $e) {
		var_dump($e);
 	}
}

 // Set up our WebSocket server for clients wanting real-time updates
$loop = React\EventLoop\Factory::create();
$socket_server = new React\Socket\Server($loop);
$socket_server->listen(8889, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$io_server = new Ratchet\Server\IoServer(
	new Ratchet\Http\HttpServer(
		new Ratchet\WebSocket\WsServer(
			new Ratchet\Wamp\WampServer(
				new Channel()
			)
		)
	),
	$socket_server
);

$loop->run();

// $server = new \Ratchet\App('localhost', 8889);
// $server->route('/', new Channel());
// $server->run();
