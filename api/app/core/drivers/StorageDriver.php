<?php

namespace Core\Drivers;

interface StorageDriver
{
	/**
	 * @param Array file
	 * @param Array options
	 * @return String public url path
	 */
	public function upload($file, $options = array());
}
