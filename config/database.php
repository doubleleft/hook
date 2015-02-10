<?php

return array(
    // Database URI
    'uri' => getenv('CLEARDB_DATABASE_URL')
        // use sqlite as default
        ?: 'sqlite://../../shared/database.sqlite?prefix=',

    // // SQLite
    // // -------
    // 'driver'   => 'sqlite',
    // 'database' => __DIR__ . '/../shared/database.sqlite',
    // 'prefix'   => '',

    // // MySQL
    // // -------
    // 'driver'   => 'mysql',
    // 'host'     => '127.0.0.1',
    // 'username' => 'root',
    // 'password' => '',
    // 'database' => 'hook',
    // 'collation' => 'utf8_general_ci',
    // 'charset' => 'utf8'

    // // PostgreSQL
    // // -------
    // 'driver'   => 'pgsql',
    // 'host'     => 'localhost',
    // 'username' => 'postgres',
    // 'charset' => 'utf-8',
    // 'password' => '',
    // 'database' => 'hook',

    // // MongoDB
    // // -------
    // 'driver'   => 'mongodb',
    // 'host'     => 'localhost',
    // 'port'     => 27017,
    // // 'username' => 'username',
    // // 'password' => 'password',
    // 'database' => 'hook'

);
