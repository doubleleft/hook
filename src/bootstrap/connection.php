<?php
$db_config = require(__DIR__ . '/../../config/database.php');

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

$resolver = new \Illuminate\Database\ConnectionResolver(array('default' => $connection));
$resolver->addConnection('app', $connection);
$resolver->setDefaultConnection('default');

DLModel::setConnectionResolver($resolver);
DLModel::setEventDispatcher($event_dispatcher);

//
// Setup paginator
//
$connection->setPaginator(new API\Pagination\Environment());

//
// Setup cache manager
//
$connection->setCacheManager(function () {
    return null;
});

//
// TODO: Create `dl-api migrate` command.
// --------------------------------------
//
// //
// // Try to create schema.
// // Ignore NoSQL databases.
// //
// if ($connection->getPdo()) {
// 	$builder = $connection->getSchemaBuilder();
// 	if (!$builder->hasTable('apps')) {
// 		foreach (glob(__DIR__ . '/../models/schema/*.php') as $file) {
// 			$migration = require($file);
// 			$builder->create(key($migration), current($migration));
// 		}
// 	}
// }
