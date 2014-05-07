<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'log.enabled' => true
));

// database
require __DIR__ . '/bootstrap/connection.php';

// core
require __DIR__ . '/core/functions.php';

return $app;
