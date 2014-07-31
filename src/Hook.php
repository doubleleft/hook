<?php
// global helpers
require __DIR__ . '/bootstrap/helpers.php';

$config = array();
$preferences = require(__DIR__ . '/../config/preferences.php');
date_default_timezone_set($preferences['timezone']);

if ($preferences['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$config['debug'] = $preferences['debug'];
$config['log.enabled'] = $preferences['debug'];

// Merge settings with security config
$config = array_merge($config, require(__DIR__ . '/../config/security.php'));

$app = new \Slim\Slim($config);

// database
require __DIR__ . '/bootstrap/connection.php';

return $app;
