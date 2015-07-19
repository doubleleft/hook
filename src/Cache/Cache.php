<?php namespace Hook\Cache;

use Hook\Security\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Cache\CacheManager;

class ConfigContainer extends \ArrayObject {
    public function bound() { return false; }
}

/**
 * Cache - Proxy class to Illuminate\Cache\StoreInterface
 * @see \Illuminate\Cache\StoreInterface
 */
class Cache
{
    protected static $instance = null;
    protected static $manager = null;

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = static::getCacheDriver();
        }

        return static::$instance;
    }

    public static function getManager($driver=null)
    {
        if (!static::$manager) {

            if (!$driver) {
                $driver = \Slim\Slim::getInstance()->config('cache');
            }

            if ($driver == "filesystem") {
                $config = array(
                    'files' => new Filesystem(),
                    'config' => array(
                        'cache.driver' => 'file',
                        'cache.path' => storage_dir() . '/cache'
                    )
                );

            } else if ($driver == "database") {
                $config = array(
                    'db' => \DLModel::getConnectionResolver(),
                    'encrypter' => Encrypter::getInstance(),
                    'config' => array(
                        'cache.default' => 'database',
                        'cache.prefix' => '',
                        'cache.stores.database' => array(
                            'driver' => 'database',
                            'connection' => 'default',
                            'table' => 'cache',
                        )
                    )
                );
            }
            $container = new ConfigContainer($config);

            static::$manager = new CacheManager($container);
        }
        return static::$manager;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(static::getInstance(), $name), $arguments);
    }

    protected static function getCacheDriver()
    {
        $driver = \Slim\Slim::getInstance()->config('cache');
        return static::getManager($driver)->driver($driver);
    }

}
