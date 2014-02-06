<?php

return array(

	// 'mongodb' => array(
	// 	'driver'   => 'mongodb',
	// 	'host'     => 'localhost',
	// 	'port'     => 27017,
	// 	// 'username' => 'username',
	// 	// 'password' => 'password',
	// 	'database' => 'dl_api'
	// ),

	'mysql' => array(
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'username' => 'root',
		'password' => 'root',
		'database' => 'dl-api',
		'collation' => 'utf8_general_ci',
		'charset' => 'utf8'
	),

	// 'sqlite' => array(
	// 	'driver'   => 'sqlite',
	// 	'database' => __DIR__ . '/../storage/database.sqlite',
	// 	'prefix'   => '',
	// )

);
