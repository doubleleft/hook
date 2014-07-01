<?php
namespace API\Model;

use API\Database\CollectionDelegator as CollectionDelegator;

/**
 * App
 */
class App extends Model
{

    public static function boot()
    {
        parent::boot();
        static::created(function ($instance) { $instance->afterCreate(); });
    }

    /**
     * currentId
     * @static
     * @return int
     */
    public static function currentId()
    {
        $app = \Slim\Slim::getInstance();

        return $app->key->app_id;
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
        return $this->hasMany('API\Model\AppKey', 'app_id');
    }

    public function modules()
    {
        return $this->hasMany('API\Model\Module', 'app_id');
    }

    public function configs()
    {
        return $this->hasMany('API\Model\AppConfig', 'app_id');
    }

    public function generate_key($admin=false)
    {
        return $this->keys()->create(array('admin' => $admin));
    }

    public function afterCreate()
    {
        // Generate admin key
        $this->generate_key(true);

        // Generate user key
        $this->generate_key();

        // Create storage directory for this app
        mkdir(storage_dir(true, $this->_id), 0777, true);
    }

    /**
     * current
     *
     * @example
     *     App::current()->where('name', 'like', 'mail.%')->get()
     *
     * @static
     * @return Model\App
     */
    public function scopeCurrent($query)
    {
        return $query->where('_id', static::currentId());
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['keys'] = $this->keys->toArray();

        return $arr;
    }

}
