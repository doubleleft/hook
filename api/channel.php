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

$websocket = new Hoa\Websocket\Server(new Hoa\Socket\Server('tcp://127.0.0.1:8889'));
$websocket->on('open', function (Hoa\Core\Event\Bucket $bucket) {
	echo 'new connection', "\n";
	return;
});

$websocket->on('message', function (Hoa\Core\Event\Bucket $bucket) {
	$data = $bucket->getData();
	echo '> message ', $data['message'], "\n";
	$bucket->getSource()->send($data['message']);

	// $bucket->getSource()->getConnection()->getNodes();
	return;
});

$websocket->on('close', function (Hoa\Core\Event\Bucket $bucket) {
	echo 'connection closed', "\n";
	return;
});
$websocket->run();
