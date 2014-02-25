<?php
namespace Storage\Providers;

class Filesystem extends Base {

	public function upload($path, $file, $options=array()) {
		$filename = md5($file['name']) . uniqid() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
		if(!is_dir($path)){
			mkdir($path, 0777, true);
		}
		$dest = $path . '/' . $filename;
		if(move_uploaded_file($file['tmp_name'], $dest)){
			return $filename;
		}else{
			return NULL;
		}
	}

}
