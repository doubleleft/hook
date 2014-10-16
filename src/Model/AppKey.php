<?php
namespace Hook\Model;

use Hook\Database\AppContext;

/**
 * AppKey
 */
class AppKey extends Model
{
    protected $guarded = array('key');

    const TYPE_CLI = 'cli';
    const TYPE_BROWSER = 'browser';
    const TYPE_SERVER = 'server';
    const TYPE_DEVICE = 'device';

    public static function boot()
    {
        parent::boot();
        static::saving(function ($instance) { $instance->beforeSave(); });
    }

    public function app() {
        return $this->belongsTo('Hook\Model\App');
    }

    public function isBrowser()
    {
        return $this->type == self::TYPE_BROWSER;
    }

    public function isServer()
    {
        return $this->type == self::TYPE_SERVER;
    }

    public function isDevice()
    {
        return $this->type == self::TYPE_DEVICE;
    }

    public function isCommandline()
    {
        return $this->type == self::TYPE_CLI;
    }

    public function beforeSave()
    {
        // creating the key
        if (!$this->key) {
            $this->key = md5(uniqid(rand(), true));
        }
    }

    public function __callStatic($method, $arguments) {
        return call_user_func_array(array(AppContext::getKey(), $name), $arguments);
    }

}
