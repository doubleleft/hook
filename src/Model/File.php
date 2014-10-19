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
        if (is_string($data) && preg_match('/data:[a-z]+\/([a-z]+);base64,([^$]+)/', $data, $base64)) {
            return $base64;
        }

        return false;
    }

    public function beforeCreate()
    {
        if ($this->file) {
            $provider = Config::get('storage.provider', 'filesystem');

            if ($base64 = static::base64($this->file)) {
                $this->name = "base64" . uniqid() . '.' . $base64[1];
                $this->mime = $base64[1];
                $this->path = Provider::get($provider)->store($this->name, base64_decode($base64[2]), array(
                    'mime' => $this->mime // some storage providers need to know the file mime type
                ));

            } else {
                $this->name = $this->file['name'];
                $this->mime = $this->file['type'];
                $this->path = Provider::get($provider)->upload($this->file, array(
                    'mime' => $this->mime // some storage providers need to know the file mime type
                ));
            }
            unset($this->attributes['file']);
        }
    }

}
