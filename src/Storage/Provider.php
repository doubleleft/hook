<?php
namespace API\Storage;

class Provider
{
    // available providers
    static $list = array(
        'filesystem' => 'API\\Storage\\Providers\\Filesystem',
        's3' => 'API\\Storage\\Providers\\S3',
        'dropbox' => 'API\\Storage\\Providers\\Dropbox'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
