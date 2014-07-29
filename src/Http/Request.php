<?php namespace Hook\Http;

class Request {

    public static function server($name) {
        return Router::getInstance()->environment->offsetGet($name);
    }

    public static function header($name, $default = null) {
        return Router::getInstance()->request->headers->get($name, $default);
    }

    public static function method() {
        return Router::getInstance()->request->getMethod();
    }

}
