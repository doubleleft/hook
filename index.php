<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/src/Hook.php';
Hook\Http\Router::setup($app)->run();
