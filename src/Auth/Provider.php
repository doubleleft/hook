<?php
namespace API\Auth;

class Provider
{
    // available providers
    static $list = array(
        'facebook' => 'API\\Auth\\Providers\\Facebook',
        'twitter' => 'API\\Auth\\Providers\\Twitter',
        'email' => 'API\\Auth\\Providers\\Email',
        'google' => 'API\\Auth\\Providers\\Google',
        'github' => 'API\\Auth\\Providers\\Github'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
