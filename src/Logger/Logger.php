<?php namespace Hook\Logger;

use Hook\Http\Router;

class Logger {

    public static function log($message) {
        return static::info($message);
    }

    public static function info($message) {
        return Router::getInstance()->log->info(to_json($message));
    }

    public static function error($message) {
        return Router::getInstance()->log->error(to_json($message));
    }

    public static function debug($message) {
        return Router::getInstance()->log->debug(to_json($message));
    }

    public static function warn($message) {
        return Router::getInstance()->log->warn(to_json($message));
    }

    public static function notice($message) {
        return Router::getInstance()->log->notice(to_json($message));
    }

}
