<?php
namespace Hook\Auth;

class Provider
{
    // available providers
    static $list = array(
        'facebook' => 'Hook\\Auth\\Providers\\Facebook',
        'twitter' => 'Hook\\Auth\\Providers\\Twitter',
        'email' => 'Hook\\Auth\\Providers\\Email',
        'google' => 'Hook\\Auth\\Providers\\Google',
        'github' => 'Hook\\Auth\\Providers\\Github'
    );

    public static function get($name)
    {
        return new self::$list[$name];
    }
}
