<?php namespace Hook\Logger;

use Hook\Http\Router;

class Logger {

    public static function log($message) {
        return static::info($message);
    }

    public static function info($message) {
        return Router::getInstance()->log->info("[INFO] " . to_json($message));
    }

    public static function error($message) {
        return Router::getInstance()->log->error("[ERROR] " . to_json($message));
    }

    public static function debug($message) {
        return Router::getInstance()->log->debug("[DEBUG] " . to_json($message));
    }

    public static function warn($message) {
        return Router::getInstance()->log->warn("[WARN] " . to_json($message));
    }

    public static function notice($message) {
        return Router::getInstance()->log->notice("[NOTICE] " . to_json($message));
    }

}
