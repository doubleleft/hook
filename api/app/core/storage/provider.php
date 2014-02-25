<?php

namespace Storage;

class Provider {

	// available providers
	static $list = array(
		'filesystem' => 'Storage\\Providers\\Filesystem',
		's3' => 'Storage\\Providers\\S3',
		'dropbox' => 'Storage\\Providers\\Dropbox'
	);

	public static function get($name) {
		return new self::$list[$name];
	}
}
