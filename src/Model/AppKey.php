<?php
namespace Hook\Model;

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

    public static function boot()
    {
        parent::boot();
        static::saving(function ($instance) { $instance->beforeSave(); });
    }

    public function beforeSave()
    {
        if (!$this->key) {
            $this->key = md5(uniqid(rand(), true));
        }

        // if ($this->key && $this->secret) { return; }

        // $res = openssl_pkey_new(array(
        // 	"digest_alg" => "sha1",
        // 	"private_key_bits" => 512,
        // 	"private_key_type" => OPENSSL_KEYTYPE_RSA,
        // ));
    //
        // // Extract the public key from $res to $pubKey
        // $public_key = openssl_pkey_get_details($res);

        // $this->key    = md5($public_key['rsa']['dmq1']);
        // $this->secret = md5($public_key['rsa']['iqmp']);
    }
}
