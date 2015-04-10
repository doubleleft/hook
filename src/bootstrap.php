<?php
$app = require __DIR__ . '/Hook.php';

Hook\Http\Router::setInstance($app);

require __DIR__ . '/bootstrap/connection.php';

foreach($app['settings']['aliases'] as $alias => $source) {
    class_alias($source, $alias);
}

return $app;
