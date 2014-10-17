<?php

return array(
    /**
     * Application timezone
     * --------------------
     */
    'timezone' => 'America/Sao_Paulo',

    /**
     * Debug mode
     * ----------
     * If true, outputs all database queries and
     * requests on application's log file.
     */
    'debug' => true,

    /**
     * Cache
     * -----
     *
     * Available options:
     *
     * - filesystem
     * - database
     */
    'cache' => 'database',

    // Global aliases
    'aliases' => array(
        // Hook\Model
        'App' => 'Hook\\Model\\App',
        'AppKey' => 'Hook\\Model\\AppKey',
        'Config' => 'Hook\\Model\\AppConfig',
        'AppContext' => 'Hook\\Database\\AppContext',

        // Hook\Http
        'Cookie' => 'Hook\\Http\\Cookie',
        'Input' => 'Hook\\Http\\Input',
        'Request' => 'Hook\\Http\\Request',
        'Router' => 'Hook\\Http\\Router',

        // Utils
        'Mail' => 'Hook\\Mailer\\Mail'
    )
);
