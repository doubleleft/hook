<?php
namespace Storage\Providers;
use \models\App as App;

class Filesystem extends Base {

	public function upload($file, $options=array()) {
		$filename = md5($file['name']) . uniqid() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
		$public_dir = 'storage/files/' . App::currentId() . '/';

		$dir = __DIR__ . '/../../../' . $public_dir;
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
			$path = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
			return 'http://' . $_SERVER['SERVER_NAME'] . $path . 'app/' . $public_dir . $filename;
		}
	}

}
