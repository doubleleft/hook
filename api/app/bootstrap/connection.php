<?php

$config = require('../app/config/database.php');

$container = new Illuminate\Container\Container();
$event_dispatcher = new Illuminate\Events\Dispatcher($container);

// ------------------
// MongoDB connection
// ------------------
// $connection = new Jenssegers\Mongodb\Connection($config['mongodb']);
// class_alias('\Jenssegers\Mongodb\Model', 'DLModel');


//
// Create SQLite database
//
if (isset($config['sqlite'])) {
	touch($config['sqlite']['database']);
}

// -------------
// SQL connection
// --------------
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
// $connection = $connFactory->make($config['mysql']);
$connection = $connFactory->make($config['sqlite']);
class_alias('\Illuminate\Database\Eloquent\Model', 'DLModel');

$resolver = new \Illuminate\Database\ConnectionResolver(array('default' => $connection));
$resolver->addConnection('app', $connection);
$resolver->setDefaultConnection('default');

DLModel::setConnectionResolver($resolver);
DLModel::setEventDispatcher($event_dispatcher);

//
// Setup paginator
//
$connection->setPaginator(new \Core\Pagination\Environment());

//
// Try to migrate the database
//
$builder = $connection->getSchemaBuilder();
if (!$builder->hasTable('apps')) {
	foreach(glob('../app/models/schema/*.php') as $file) {
		$migration = require($file);
		$builder->create(key($migration), current($migration));
	}
}
