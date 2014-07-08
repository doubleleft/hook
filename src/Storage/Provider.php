<?php
namespace Hook\Storage;

class Provider
{
    // available providers
    static $list = array(
        'filesystem' => 'Hook\\Storage\\Providers\\Filesystem',
        's3' => 'Hook\\Storage\\Providers\\S3',
        'dropbox' => 'Hook\\Storage\\Providers\\Dropbox'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
