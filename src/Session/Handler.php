<?php namespace Hook\Session;

//
// TODO:
//
// session_id must be kept both on hook-javascript and
// custom routes directly (OAuth)
//
// Session handlers are not working yet.
// We must write a test-case for each handler.
//

use Hook\Http\Request;
use Hook\Http\Response;

class Handler {
    // available session handlers
    static $handlers = array(
        'database' => "Hook\\Session\\Handlers\\Database",
        'memory' => "Hook\\Session\\Handlers\\Memory",
        'redis' => "Hook\\Session\\Handlers\\Redis",
        'amazon_aws' => "Hook\\Session\\Handlers\\AmazonAWS",
        'windows_azure' => "Hook\\Session\\Handlers\\WindowsAzure",
    );

    public static function register($handler_name) {
        $handler = new self::$handlers[$handler_name];

        ini_set('session.use_cookies', 0);
        session_cache_limiter(false);

        // Register the session handler, with shutdown function
        session_set_save_handler($handler, TRUE);

        if (session_id() === '') {
            session_start();
        }

        // TODO:
        // ini_set('session.gc_probability', $auto_garbage_collection);

        return $handler;
    }
}

