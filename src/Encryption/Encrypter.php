<?php namespace Hook\Encryption;

use Hook\Database\AppContext as AppContext;

class Encrypter
{
    protected static $instance;
    protected static $method = "AES-256-CBC";

    protected $private_key;
    protected $iv;

    public static function getInstance()
    {
        if (!static::$instance) { static::$instance = new static(); }
        return static::$instance;
    }

    public function __construct()
    {
        $app_key = AppContext::getKey();

        $secret_key = $app_key->app->secret;
        $secret_iv = sha1($secret_key . $app_key->key);

        // private_key
        $this->private_key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $this->iv = substr(hash('sha256', $secret_iv), 0, 16);
    }

    public function encrypt($data)
    {
        return base64_encode(openssl_encrypt($data, static::$method, $this->private_key, 0, $this->iv));
    }

    public function decrypt($data)
    {
        return openssl_decrypt(base64_decode($data), static::$method, $this->private_key, 0, $this->iv);
    }

}
