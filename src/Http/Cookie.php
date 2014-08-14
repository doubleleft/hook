<?php namespace Hook\Http;

class Cookie {

    /**
     * get
     *
     * @param mixed $name
     * @param mixed $default
     */
    public static function get($name = null, $default = null)
    {
        if (is_null($name)) {
            return Request::cookies()->all();
        } else {
            return Router::getInstance()->getCookie($name) ?: $default;
        }
    }

    /**
     * set
     *
     * @param mixed $name
     * @param mixed $value
     * @param mixed $time
     * @param mixed $path
     * @param mixed $domain
     * @param mixed $secure
     * @param mixed $httponly
     */
    public static function set($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return Router::getInstance()->setCookie($name, $value, $time, $path, $domain, $secure, $httponly);
    }

    /**
     * delete
     *
     * @param mixed $name
     * @param mixed $path
     * @param mixed $domain
     * @param mixed $secure
     * @param mixed $httponly
     */
    public static function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return Router::getInstance()->deleteCookie($name, $path, $domain, $secure, $httponly);
    }

}
