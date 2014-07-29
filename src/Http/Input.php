<?php namespace Hook\Http;

class Input {

    public static function get($name = null, $default = null) {
        return Router::getInstance()->request()->get($name, $default);
    }

}

