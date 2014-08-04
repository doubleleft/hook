<?php namespace Hook\Cache;

/**
 * Cache - Proxy class to Illuminate\Cache\StoreInterface
 * @see \Illuminate\Cache\StoreInterface
 */
class Cache
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (!static::$instance) {
            $connection = \DLModel::getConnectionResolver()->connection();
            $cache_manager = $connection->getCacheManager();
            $default_driver = $cache_manager->getDefaultDriver();
            static::$instance = $cache_manager->driver($default_driver);
        }

        return static::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(static::getInstance(), $name), $arguments);
    }
}
