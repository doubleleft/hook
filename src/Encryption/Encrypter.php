<?php namespace Hook\Encryption;

use Hook\Database\AppContext as AppContext;

class Encrypter extends \Illuminate\Encryption\Encrypter
{
    protected static $instance;

    protected $cipher = "AES-256-CBC";
    protected $blocks = 16;
    protected $private_key;
    protected $iv;

    public static function getInstance()
    {
        $app_key = AppContext::getKey();

        if (!static::$instance && $app_key) {
            static::$instance = new static($app_key->app->secret, $app_key->app_id);
        }

        return static::$instance;
    }

    public function __construct($key, $salt)
    {
        // private_key
        $this->private_key = hash('sha256', $key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $this->iv = substr(hash('sha256', $salt), 0, $this->blocks);
    }

    public function encrypt($data)
    {
        return base64_encode(openssl_encrypt(json_encode($data), $this->cipher, $this->private_key, 0, $this->iv));
    }

    public function decrypt($data)
    {
        return json_decode(openssl_decrypt(base64_decode($data), $this->cipher, $this->private_key, 0, $this->iv), true);
    }

}
