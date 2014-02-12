<?php

namespace Client;

class Utils {

	public static function glob($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::glob($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}

}
