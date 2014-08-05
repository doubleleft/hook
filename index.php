<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/src/Hook.php';
$app->config('database', require(__DIR__ . '/config/database.php'));
require __DIR__ . '/src/bootstrap/connection.php';
Hook\Http\Router::setup($app)->run();
