<?php namespace Hook\Database\Schema;

use Hook\Cache\Cache as Store;
use Hook\Security\Encryption\Encrypter as Encrypter;

use Illuminate\Cache\CacheManager;

class Cache
{
    protected static $store;
    protected static $local = array();

    public static function forever($collection, $value) {
        static::$local[ $collection ] = $value;
        return static::getStore()->forever($collection, $value);
    }

    public static function get($collection) {
        if (!isset(static::$local[ $collection ])) {
            static::$local[ $collection ] = static::getStore()->get($collection) ?: array();
        }
        return static::$local[ $collection ];
    }

    public static function getStore() {
        if (!static::$store) {
            $manager = new CacheManager(array(
                'encrypter' => Encrypter::getInstance(),
                'db' => \DLModel::getConnectionResolver(),
                'config' => array(
                    'cache.driver' => 'database',
                    'cache.connection' => 'default',
                    'cache.table' => 'cache',
                    'cache.prefix' => 'schema_'
                )
            ));
            static::$store = $manager->driver('database')->getStore();
        }
        return static::$store;
    }

}
