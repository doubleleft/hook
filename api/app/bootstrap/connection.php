<?php

$config = require('../app/config/database.php');

$container = new Illuminate\Container\Container();
$event_dispatcher = new Illuminate\Events\Dispatcher($container);

// ------------------
// MongoDB connection
// ------------------
// $connection = new Jenssegers\Mongodb\Connection($config['mongodb']);
// $resolver->addConnection('app', $connection);
// $resolver->setDefaultConnection('default');
//
// \Jenssegers\Mongodb\Model::setConnectionResolver($resolver);
// \Jenssegers\Mongodb\Model::setEventDispatcher($event_dispatcher);

// -------------
// SQL connection
// --------------
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$connection = $connFactory->make($config['mysql']);

$resolver = new \Illuminate\Database\ConnectionResolver(array('default' => $connection));
$resolver->addConnection('default', $connection);
$resolver->setDefaultConnection('default');

\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
\Illuminate\Database\Eloquent\Model::setEventDispatcher($event_dispatcher);

// Try to migrate the database
$builder = $connection->getSchemaBuilder();
if (!$builder->hasTable('apps')) {
	foreach(glob('../app/models/schema/*.php') as $file) {
		$migration = require($file);
		$builder->create(key($migration), current($migration));
	}
}
