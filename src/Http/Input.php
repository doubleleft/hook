<?php namespace Hook\Http;

class Input {
    protected static $params;

    public static function get($name = null, $default = null) {
        if (!static::$params) {
            static::$params = Router::getInstance()->request()->params();
        }

        if ($name) {
            return isset(static::$params[$name]) ? static::$params[$name] : $default;
        } else {
            return static::$params;
        }
    }

}

