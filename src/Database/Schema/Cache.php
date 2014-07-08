<?php namespace Hook\Database\Schema;

use Hook\Cache\Cache as Store;

class Cache
{

    public static function forever($collection, $value) {
        return Store::forever(static::key($collection), json_encode($value));
    }

    public static function get($collection) {
        return json_decode(Store::get(static::key($collection)), true) ?: array();
    }

    protected static function key($name) {
        return $name . '_schema';
    }

}
