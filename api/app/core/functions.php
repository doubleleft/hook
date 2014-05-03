<?php

/**
 * URL helpers
 */
function public_url($segments, $protocol = null) {
	$path = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
	$protocol = $protocol ?: (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http');
	return  $protocol . '://' . $_SERVER['SERVER_NAME'] . $path . $segments;
}

/**
 * storage_dir
 * @return string
 */
function storage_dir($relative=true) {
	return ($relative ? __DIR__ . '/../../' : '') . 'storage/files/' . models\App::currentId();
}

/**
 * storage_url
 * @return string
 */
function storage_url() {
	return public_url(storage_dir(false));
}

/**
 * Math extensions
 */
function clamp($val, $min, $max) {
	return max($min, min($max, $val));
}

/**
 * Array functions
 */
function array_diverse($vector) {
	$result = array();
	foreach($vector as $key1 => $value1) {
		foreach($value1 as $key2 => $value2) {
			$result[$key2][$key1] = $value2;
		}
	}
	return $result;
}
