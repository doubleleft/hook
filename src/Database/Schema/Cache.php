<?php namespace Hook\Database\Schema;

use Hook\Cache\Cache as Store;
use Hook\Encryption\Encrypter as Encrypter;

class Cache
{
    protected static $store;
    protected static $local_cache = array();

    public static function forever($collection, $value) {
        return static::getStore()->forever($collection, $value);
    }

    public static function get($collection) {
        if (!isset(static::$local_cache[$collection])) {
            static::$local_cache[$collection] = static::getStore()->get($collection) ?: array();
        }
        return static::$local_cache[$collection];
    }

    public static function getStore() {
        if (!static::$store) {
            $manager = new \Illuminate\Cache\CacheManager(array(
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
