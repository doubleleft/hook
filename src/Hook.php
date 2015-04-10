<?php
// global helpers
require __DIR__ . '/bootstrap/helpers.php';

$settings = require(__DIR__ . '/../config/preferences.php');
date_default_timezone_set($settings['timezone']);

if ($settings['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$settings['log.enabled'] = $settings['debug'];

// Merge settings with security settings
$settings = array_merge($settings, require(__DIR__ . '/../config/security.php'));

$settings['database'] = require(__DIR__ . '/../config/database.php');
$settings['paths'] = require(__DIR__ . '/../config/paths.php');
$settings['view'] = new Hook\View\View();

$app = new \Slim\App($settings);
return $app;
