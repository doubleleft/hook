<?php
$app = require __DIR__ . '/Hook.php';

$app->config('database', require(__DIR__ . '/../config/database.php'));
$app->config('paths', require(__DIR__ . '/../config/paths.php'));

Hook\Http\Router::setInstance($app);

require __DIR__ . '/bootstrap/connection.php';

foreach($app->config('aliases') as $alias => $source) {
    class_alias($source, $alias);
}

return $app;
