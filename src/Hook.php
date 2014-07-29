<?php
// global helpers
require __DIR__ . '/bootstrap/helpers.php';

$app = new \Slim\Slim(array(
    'log.enabled' => true
));

// apply preferences
$preferences = require(__DIR__ . '/../config/preferences.php');
if (isset($preferences['timezone'])) {
    date_default_timezone_set($preferences['timezone']);
}

// database
require __DIR__ . '/bootstrap/connection.php';

return $app;
