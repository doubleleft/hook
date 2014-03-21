<?php

namespace Client;

class Console {

	public static function output($message) {
		echo $message . PHP_EOL;
	}

	public static function error($message) {
		echo "\033[1;31m{$message}\033[0;39m" . PHP_EOL;
	}

}
