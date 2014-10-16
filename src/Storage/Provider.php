<?php
namespace Hook\Storage;

class Provider
{
    // available providers
    static $list = array(
        'filesystem' => 'Hook\\Storage\\Providers\\Filesystem',
        'amazon_aws' => 'Hook\\Storage\\Providers\\AmazonAWS',
        'windows_azure' => 'Hook\\Storage\\Providers\\WindowsAzure',
        'dropbox' => 'Hook\\Storage\\Providers\\Dropbox'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
