<?php
namespace API\Model;

/**
 * AuthToken
 */
class AuthToken extends Model
{
    const EXPIRATION = 24; // hours

    // protected $table = 'auth_tokens';
    public $timestamps = false;

    static $_current = null;

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
        return $this->belongsTo('Model\App');
    }

    public function auth()
    {
        return $this->belongsTo('Model\Auth');
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
        $this->expire_at = time() + (static::EXPIRATION * 60 * 60);
        $this->created_at = time();
        $this->token = md5(uniqid(rand(), true));
        // $this->level = 1;
    }

}
