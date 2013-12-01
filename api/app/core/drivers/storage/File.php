<?php

namespace Core\Drivers\Storage;

class File extends Core\Drivers\Driver implements Core\Drivers\StorageDriver
{
	public function upload($file, $options=array()) {
		$filename = md5($file['name']) . uniqid();
		$dest = 'storage/' . $this->app->_id . '/' . $filename;
		move_uploaded_file($file['tmpname'], $dest);
		return $dest;
	}
}
