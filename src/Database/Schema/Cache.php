<?php namespace Hook\Database\Schema;

class ConfigContainer extends \ArrayObject {
    public function bound() { return false; }
}

use Hook\Cache\Cache as Store;
use Hook\Encryption\Encrypter as Encrypter;

use Illuminate\Cache\CacheManager;

class Cache
{
    protected static $store;

    public static function forever($collection, $value) {
        return static::getStore()->forever($collection, $value);
    }

    public static function get($collection) {
        return static::getStore()->get($collection) ?: array();
    }

    public static function getStore() {
        if (!static::$store) {
            $manager = new CacheManager(new ConfigContainer(array(
                'db' => \DLModel::getConnectionResolver(),
                'encrypter' => Encrypter::getInstance(),
                'config' => array(
                    'cache.driver' => 'database',
                    'cache.prefix' => 'schema_',
                    // 'cache.connection' => 'default',
                    // 'cache.table' => 'cache',
                    'cache.stores.database' => array(
                        'driver' => 'database',
                        'connection' => 'default',
                        'table' => 'cache',
                    )
                )
            )));
            static::$store = $manager->driver('database')->getStore();
        }
        return static::$store;
    }

}
