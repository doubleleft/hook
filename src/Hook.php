<?php
// global helpers
require __DIR__ . '/bootstrap/helpers.php';

$config = require(__DIR__ . '/../config/preferences.php');
date_default_timezone_set($config['timezone']);

foreach($config['aliases'] as $alias => $source) {
    class_alias($source, $alias);
}

if ($config['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$config['log.enabled'] = $config['debug'];

// Merge settings with security config
$config = array_merge($config, require(__DIR__ . '/../config/security.php'));

$app = new \Slim\Slim($config);
return $app;
