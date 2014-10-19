<?php
namespace Hook\Auth;

class Provider
{
    // available providers
    static $list = array(
        'facebook' => 'Hook\\Auth\\Providers\\Facebook',
        'email' => 'Hook\\Auth\\Providers\\Email'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
