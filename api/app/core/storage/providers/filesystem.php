<?php
namespace Storage\Providers;

class Filesystem extends Base {

	public function upload($file, $options=array()) {
		$filename = md5($file['name']) . uniqid() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
		$public_dir = 'storage/files/';
		$dir = __DIR__ . '/../../../' . $public_dir . $app->key->app_id;

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		if(move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)){
			return '//' . $_SERVER['SERVER_NAME'] . '/app/' . $public_dir . $filename;
		} else {
			return NULL;
		}
	}

}
