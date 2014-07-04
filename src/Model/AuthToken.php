<?php
namespace API\Model;

use \Carbon\Carbon;

/**
 * AuthToken
 */
class AuthToken extends Model
{
    const EXPIRATION_HOURS = 24; // hours

    public $timestamps = false;
    static $_current = null;
    protected $dates = array('expire_at');

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) { $model->beforeCreate(); });
    }

    /**
     * current - get current active AuthToken instance
     * @static
     * @return AuthToken|null
     */
    public static function current()
    {
        if (static::$_current === null) {
            $app = \Slim\Slim::getInstance();
            static::$_current = static::where('token', $app->request->headers->get('X-Auth-Token') ?: $app->request->get('X-Auth-Token'))
                ->where('expire_at', '>=', time())
                ->first();
        }

        return static::$_current;
    }

    public function app()
    {
        return $this->belongsTo('API\Model\App');
    }

    public function auth()
    {
        return $this->belongsTo('API\Model\Auth');
    }

    /**
     * isExpired
     * @return bool
     */
    public function isExpired()
    {
        return time() > $this->expire_at;
    }

    public function beforeCreate()
    {
        $this->created_at = Carbon::now();
        $this->expire_at = Carbon::now()->addHours(static::EXPIRATION_HOURS);
        $this->token = md5(uniqid(rand(), true));
        // $this->level = 1;
    }

}
