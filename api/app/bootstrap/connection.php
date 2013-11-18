<?php

$config = require('../app/config/database.php');
$event_dispatcher = new Illuminate\Events\Dispatcher();

// ------------------
// MongoDB connection
// ------------------
$conn = new Jenssegers\Mongodb\Connection($config['mongodb']);
$resolver = new \Illuminate\Database\ConnectionResolver(array(null => $conn));
\Jenssegers\Mongodb\Model::setConnectionResolver($resolver);
\Jenssegers\Mongodb\Model::setEventDispatcher($event_dispatcher);

// -------------
// SQL connection
// --------------
// $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory();
// $conn = $connFactory->make($config);
// $resolver->addConnection('default', $conn);
// $resolver->setDefaultConnection('default');
// \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
