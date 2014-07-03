<?php
namespace API\Model;

/**
 * AppKey
 */
class AppKey extends Model
{

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
