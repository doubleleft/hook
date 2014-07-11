<?php
namespace Hook\Model;

use Hook\Database\CollectionDelegator as CollectionDelegator;
use Hook\Database\AppContext as AppContext;

/**
 * App
 */
class App extends Model
{

    public static function boot()
    {
        parent::boot();
        static::creating(function ($instance) { $instance->beforeCreate(); });
        static::created(function ($instance) { $instance->afterCreate(); });
    }

    /**
     * currentId
     * @static
     * @return int
     */
    public static function currentId()
    {
        return AppContext::getKey()->app_id;
    }

    /**
     * collection
     * @static
     * @param  mixed                        $name name
     * @return Database\CollectionDelegator
     */
    public static function collection($name)
    {
        return new CollectionDelegator($name, static::currentId());
    }

    public function keys()
    {
        return $this->hasMany('Hook\Model\AppKey', 'app_id');
    }

    public function modules()
    {
        return $this->hasMany('Hook\Model\Module', 'app_id');
    }

    public function configs()
    {
        return $this->hasMany('Hook\Model\AppConfig', 'app_id');
    }

    public function generateKey($admin=false)
    {
        return $this->keys()->create(array('admin' => $admin));
    }

    public function beforeCreate()
    {
        // Generate app secret.
        $this->secret = md5(uniqid(rand(), true));
    }

    public function afterCreate()
    {
        // Generate admin key
        $this->generateKey(true);

        // Generate and apply user key for current request context
        $this->generateKey();

        // Create storage directory for this app
        $storage_dir = storage_dir(true, $this->_id);
        if (!file_exists($storage_dir)) {
            mkdir($storage_dir, 0777, true);
        }
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['keys'] = $this->keys->toArray();

        return $arr;
    }

}
