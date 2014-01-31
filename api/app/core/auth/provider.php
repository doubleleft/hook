<?php

namespace Auth;

class Provider {

	// available providers
	static $list = array(
		'facebook' => 'Auth\\Providers\\Facebook',
		'twitter' => 'Auth\\Providers\\Twitter'
	);

	public static function get($name) {
		return new self::$list[$name];
	}
}
