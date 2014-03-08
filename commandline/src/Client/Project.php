<?php

namespace Client;

class Project {
	private static $config_file;
	private static $temp_config;

	public static function getConfigFile() {
		return self::root() . self::$config_file;
	}

	public static function setConfigFile($file) {
		self::$config_file = $file;
	}

	public static function setTempConfig($data) {
		self::$temp_config = $data;
	}

	public static function setConfig($data) {
		$data['endpoint'] = Client::getEndpoint();
		return file_put_contents(self::getConfigFile(), json_encode($data));
	}

	public static function getConfig() {
		// return temporary app config
		if (self::$temp_config !== null) {
			return self::$temp_config;
		}

		$config_file = self::getConfigFile();
		return (!file_exists($config_file)) ? array() : json_decode(file_get_contents($config_file), true);
	}

	public static function root() {
		$scm_list = array('.dl-config', '.git', '_darcs', '.hg', '.bzr', '.svn');
		$path = getcwd();

		while ($path !== '/') {
			$path .=  '/';

			foreach($scm_list as $scm) {
				if (file_exists($path . $scm)) {
					return $path;
				}
			}

			// try parent directory...
			$path = dirname($path);
		}

		return getcwd() . '/';
	}

}
