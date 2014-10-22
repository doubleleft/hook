<?php namespace Hook\Http;

class Response {

    /**
     * header
     * @param name
     * @param value
     * @return mixed
     */
    public static function header($name = null, $value = null) {
        if (!$name && !$value) {
            return Router::getInstance()->response->headers;

        } else if ($name && $value) {
            return Router::getInstance()->response->headers->set($name, $value);

        } else if ($name && !$value) {
            return Router::getInstance()->response->headers->get($name);
        }
    }

    public static function __callStatic($method, $args = array()) {
        $response = Router::getInstance()->response;
        return call_user_func_array(array($response, $method), $args);
    }

}

