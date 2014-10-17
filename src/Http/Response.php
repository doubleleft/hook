<?php namespace Hook\Http;

class Response {

    /**
     * header
     * @param name
     * @param default
     * @return string
     */
    public static function headers() {
        return Router::getInstance()->response->headers;
    }

    public static function __callStatic($method, $args = array()) {
        $response = Router::getInstance()->response;
        return call_user_func_array(array($response, $method), $args);
    }

}

