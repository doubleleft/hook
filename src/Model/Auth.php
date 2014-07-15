<?php
namespace Hook\Model;

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

    protected $table = 'auths';

    // protect from mass-assignment.
    protected $guarded = array('email', 'password', 'password_salt', 'forgot_password_token', 'forgot_password_expiration', 'deleted_at');
    protected $hidden = array('password', 'password_salt', 'forgot_password_token', 'forgot_password_expiration', 'deleted_at');

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

    public function tokens()
    {
        return $this->hasMany('Hook\Model\AuthToken', 'auth_id');
    }

    /**
     * generateToken
     * @return AuthToken
     */
    public function generateToken()
    {
        return $this->tokens()->create(array());
    }

    public function generateForgotPasswordToken()
    {
        $this->setAttribute(self::FORGOT_PASSWORD_FIELD, sha1(uniqid(rand(), true)));
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

        // only display email for the logged user
        $auth_token = AuthToken::current();
        if (!$auth_token || $auth_token->auth_id != $this->_id) {
            unset($arr['email']);
        }

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

    //
    // Hooks
    //

    public function beforeSave()
    {
        if ($this->isDirty('password')) {
            $this->password_salt = sha1(uniqid(rand(), true));
            $this->password = static::password_hash($this->password, $this->password_salt);
        }
         parent::beforeSave();
    }

    /**
     * Generate sha1 hash of a password, using 'salt' and 'pepper' (AppConfig)
     *
     * @static
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public static function password_hash($password, $salt)
    {
        $app_auth_pepper = AppConfig::get('auth_pepper') ?: '';
        return sha1($password . $salt . $app_auth_pepper);
    }

}
