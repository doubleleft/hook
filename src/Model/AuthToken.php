<?php
namespace Hook\Model;

use Hook\Http\Request;
use Hook\Http\Input;

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
            static::$_current = static::where('token', Request::header('X-Auth-Token', Input::get('X-Auth-Token')))
                ->where('expire_at', '>=', Carbon::now())
                ->first();
        }

        return static::$_current;
    }

    public function auth()
    {
        return $this->belongsTo('Hook\Model\Auth');
    }

    /**
     * isExpired
     * @return bool
     */
    public function isExpired()
    {
        return Carbon::now() > $this->expire_at;
    }

    public function beforeCreate()
    {
        $this->created_at = Carbon::now();
        $this->expire_at = Carbon::now()->addHours(static::EXPIRATION_HOURS);
        $this->token = sha1(uniqid(rand(), true));
        // $this->level = 1;
    }

}
