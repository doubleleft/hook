<?php
$app = require __DIR__ . '/Hook.php';

$app->config('database', require(__DIR__ . '/../config/database.php'));
$app->config('paths', require(__DIR__ . '/../config/paths.php'));

require __DIR__ . '/bootstrap/connection.php';
return $app;
