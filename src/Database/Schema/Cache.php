<?php namespace API\Database\Schema;

use API\Cache\Cache as Store;

class Cache
{

    public static function forever($key, $value) {
        return Store::forever($key, json_encode($value));
    }

    public static function get($key) {
        return json_decode(Store::get($key));
    }

}
