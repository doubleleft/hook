<?php
namespace API\Auth;

class Provider
{
    // available providers
    static $list = array(
        'facebook' => 'Auth\\Providers\\Facebook',
        'twitter' => 'Auth\\Providers\\Twitter',
        'email' => 'Auth\\Providers\\Email',
        'google' => 'Auth\\Providers\\Google',
        'github' => 'Auth\\Providers\\Github'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
