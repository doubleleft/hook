<?php
namespace API\Model;

/**
 * Auth
 *
 * @uses Collection
 */
class Auth extends Collection
{
    const FORGOT_PASSWORD_FIELD = 'forgot_password_token';
    const FORGOT_PASSWORD_EXPIRATION_FIELD = 'forgot_password_expiration';
    const FORGOT_PASSWORD_EXPIRATION_TIME = 14400; // (60 * 60 * 4) = 4 hours

    protected $table = 'auth';

    static $_current = null;

    /**
     * current - get current active Auth instance
     * @static
     * @return Auth|null
     */
    public static function current()
    {
        if (static::$_current === null) {
            if ($token = AuthToken::current()) {
                static::$_current = $token->auth;
            }
        }

        return static::$_current;
    }

    public function app()
    {
        return $this->belongsTo('Model\App');
    }

    public function tokens()
    {
        return $this->hasMany('Model\AuthToken', 'auth_id');
    }

    /**
     * generateToken
     * @return AuthToken
     */
    public function generateToken()
    {
        return $this->tokens()->create(array(
            'app_id' => $this->app_id
        ));
    }

    public function generateForgotPasswordToken()
    {
        $this->setAttribute(self::FORGOT_PASSWORD_FIELD, md5(uniqid(rand(), true)));
        $this->setAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD, time() + self::FORGOT_PASSWORD_EXPIRATION_TIME);
        $this->save();

        return $this;
    }

    /**
     * resetPassword
     * @param  mixed $newPassword newPassword
     * @return void
     */
    public function resetPassword($newPassword)
    {
        $success = false;
        if (!$this->isForgotPasswordTokenExpired()) {
            $this->password = $newPassword;
            $this->setAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD, time()); // expire token
            $success = $this->save();
        }

        return $success;
    }

    protected function isForgotPasswordTokenExpired()
    {
        return time() > $this->getAttribute(self::FORGOT_PASSWORD_EXPIRATION_FIELD);
    }

    public function toArray()
    {
        $arr = parent::toArray();

        /**
         * FIXME: find other way to hide password / tokens from authentication
         */
        if (isset($arr['password'])) { unset($arr['password']); }
        if (isset($arr[self::FORGOT_PASSWORD_FIELD])) { unset($arr[self::FORGOT_PASSWORD_FIELD]); }
        if (isset($arr[self::FORGOT_PASSWORD_EXPIRATION_FIELD])) { unset($arr[self::FORGOT_PASSWORD_EXPIRATION_FIELD]); }

        return $arr;
    }

    /**
     * dataWithToken
     * @return array
     */
    public function dataWithToken()
    {
        $data = $this->toArray();
        $data['token'] = $this->generateToken()->toArray();

        return $data;
    }

}
