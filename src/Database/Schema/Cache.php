<?php namespace Hook\Database\Schema;

use Hook\Cache\Cache as Store;
use Hook\Encryption\Encrypter as Encrypter;

class Cache
{

    public static function forever($collection, $value) {
        return Store::forever($collection, json_encode($value));
    }

    public static function get($collection) {
        return json_decode(Store::get($collection), true) ?: array();
    }

    public static function getStore() {
        $manager = new \Illuminate\Cache\CacheManager(array(
            'encrypter' => Encrypter::getInstance(),
            'db' => \DLModel::getConnectionResolver(),
            'config' => array(
                'cache.driver' => 'database',
                'cache.connection' => 'default',
                'cache.table' => 'cache',
                'cache.prefix' => 'schema'
            )
        ));
        return $manager->driver('database');
    }

}
