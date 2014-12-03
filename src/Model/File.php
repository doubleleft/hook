<?php
namespace Hook\Model;

use Hook\Storage\Provider;
use Hook\Application\Config;

/**
 * Store file upload references.
 */
class File extends Model
{
    public static function boot()
    {
        static::creating(function ($m) { $m->beforeCreate(); });
    }

    public static function base64($data)
    {
        if (is_string($data) && preg_match('/data:([a-z\.-]+\/[a-z\.-]+);([\ ]?charset=[a-z]+;)?base64,([^$]+)/', $data, $base64)) {
            return $base64;
        }

        return false;
    }

    public function read() {
        $provider = Config::get('storage.provider', 'filesystem');
        return Provider::get($provider)->read($this);
    }

    public function getRealpathAttribute() {
        $provider = Config::get('storage.provider', 'filesystem');
        return Provider::get($provider)->realpath($this);
    }

    public function beforeCreate()
    {
        if ($this->file) {
            $provider = Config::get('storage.provider', 'filesystem');
            $contents = null;

            if ($base64 = static::base64($this->file)) {
                preg_match('/\/([a-z\.-]+)/', $base64[1], $ext);
                $extension = $ext[1];

                $this->name = sha1(uniqid(rand(), true)) . '.' . $extension;
                $this->mime = $base64[1];
                $contents = base64_decode($base64[3]);
            } else {
                $this->name = md5($this->file['name']) . uniqid() . "." . pathinfo($this->file['name'], PATHINFO_EXTENSION);
                $this->mime = $this->file['type'];
                $contents = file_get_contents($this->file['tmp_name']);
            }

            $this->path = Provider::get($provider)->store($this->name, $contents, array(
                'mime' => $this->mime // some storage providers need to know the file mime type
            ));
            unset($this->attributes['file']);
        }
    }

}
