<?php
namespace Storage\Providers;

class Filesystem extends Base {

	public function upload($file, $options=array()) {
		$filename = md5($file['name']) . uniqid();
		$dest = __DIR__ . '/../../storage/' . $this->app->_id . '/' . $filename;
		move_uploaded_file($file['tmpname'], $dest);
		return $dest;
	}

}
