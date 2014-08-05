<?php
$db_config = \Slim\Slim::getInstance()->config('database');

$container = new Illuminate\Container\Container();
$event_dispatcher = new Illuminate\Events\Dispatcher($container);

// ------------------
// MongoDB connection
// ------------------
if ($db_config['driver'] == 'mongodb') {
    $connection = new Jenssegers\Mongodb\Connection($db_config);
    class_alias('\Jenssegers\Mongodb\Model', 'DLModel');

} else {

    //
    // Create SQLite database
    //
    if ($db_config['driver'] == 'sqlite') {
        touch($db_config['database']);
    }

    // -------------
    // SQL connection
    // --------------
    $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
    $connection = $connFactory->make($db_config);

    $connection->setFetchMode(PDO::FETCH_CLASS);
    class_alias('\Illuminate\Database\Eloquent\Model', 'DLModel');
}

//
// Setup default date format
// Use a string representing an RFC2822 or ISO 8601 date
// http://tools.ietf.org/html/rfc2822#page-14
//
\Carbon\Carbon::setToStringFormat('Y-m-d\TH:i:sP');

$resolver = new \Illuminate\Database\ConnectionResolver(array('default' => $connection));
$resolver->addConnection('app', $connection);
$resolver->setDefaultConnection('default');

DLModel::setConnectionResolver($resolver);
DLModel::setEventDispatcher($event_dispatcher);

// Setup paginator
$connection->setPaginator(new Hook\Pagination\Environment());
$connection->setEventDispatcher($event_dispatcher);

// Setup Schema Grammar
// $connection->setSchemaGrammar();

// Setup cache manager
$connection->setCacheManager(function () {
    $cache_driver = \Slim\Slim::getInstance()->config('cache');

    if ($cache_driver == "filesystem") {
        $config = array(
            'files' => new \Illuminate\Filesystem\Filesystem(),
            'config' => array(
                'cache.driver' => 'file',
                'cache.path' => storage_dir() . '/cache'
            )
        );

    } else if ($cache_driver == "database") {
        $config = array(
            'db' => \DLModel::getConnectionResolver(),
            'encrypter' => Hook\Encryption\Encrypter::getInstance(),
            'config' => array(
                'cache.driver' => 'database',
                'cache.connection' => 'default',
                'cache.table' => 'cache',
                'cache.prefix' => ''
            )
        );
    }

    return new Illuminate\Cache\CacheManager($config);
});

//
// TODO: Create `hook migrate` command.
// --------------------------------------
//
//
// Try to create schema.
// Ignore NoSQL databases.
//
if ($connection->getPdo()) {
    $builder = $connection->getSchemaBuilder();
    if (!$builder->hasTable('apps')) {
        foreach (glob(__DIR__ . '/../../migrations/global/*.php') as $file) {
            $migration = require($file);
            $builder->create(key($migration), current($migration));
        }
    }
}
