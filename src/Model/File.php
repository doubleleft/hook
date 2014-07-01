<?php
namespace API\Model;

use API\Storage\Provider as Provider;

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

    public function app()
    {
        return $this->belongsTo('API\Model\App');
    }

    public function beforeCreate()
    {
        if ($this->file) {
            $provider = AppConfig::get('storage.provider', 'filesystem');

            if ($base64 = static::base64($this->file)) {
                $this->name = "base64" . uniqid() . '.' . $base64[1];
                $this->path = Provider::get($provider)->store($this->name, base64_decode($base64[2]));
                $this->mime = $base64[1];

            } else {
                $this->name = $this->file['name'];
                $this->mime = $this->file['type'];
                $this->path = Provider::get($provider)->upload($this->file);
            }
            unset($this->attributes['file']);
        }
    }

}
