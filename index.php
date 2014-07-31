<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/src/Hook.php';
Hook\Http\Router::setup($app)->run();
