<?php

return array(

	'mongodb' => array(
		'driver'   => 'mongodb',
		'host'     => 'localhost',
		'port'     => 27017,
		// 'username' => 'username',
		// 'password' => 'password',
		'database' => 'dl_api'
	),

	'mysql' => array(
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'username' => 'root',
		'password' => 'root',
		'database' => 'dl_api',
		'collation' => 'utf8_general_ci',
		'charset' => 'utf8'
	),

);
