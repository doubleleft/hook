<?php

/**
 * Globally available helper functions
 */

/**
 * URL helpers
 */
function public_url($segments, $protocol = null)
{
    $path = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
    $protocol = $protocol ?: (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http');

    return  $protocol . '://' . $_SERVER['SERVER_NAME'] . $path . $segments;
}

/**
 * debug
 *
 * @param mixed $data
 */
function debug($data)
{
    if (is_string($data)) {
        $text = $data;
    } else if (method_exists($data, 'toJson')) {
        $text = $data->toJson();
    } else {
        $text = json_encode($data);
    }

    $app = \Slim\Slim::getInstance();

    return $app->log->info($text);
}

/**
 * storage_dir
 *
 * @param bool $relative
 * @param int $app_id
 */
function storage_dir($relative=true, $app_id = null)
{
    if (!$app_id) {
        $app_id = Hook\Model\App::currentId();
    }

    return ($relative ? __DIR__ . '/../../' : '') . 'storage/files/' . $app_id;
}

/**
 * shared_storage_dir
 *
 * @param mixed $relative
 * @param mixed $app_id
 */
function shared_storage_dir()
{
    return __DIR__ . '/../../storage';
}

/**
 * storage_url
 * @return string
 */
function storage_url()
{
    return public_url(storage_dir(false));
}

/**
 * Math extensions
 */
function clamp($val, $min, $max)
{
    return max($min, min($max, $val));
}

/**
 * Array functions
 */

/**
 * Removes an item from the array and returns its value.
 *
 * @param array $arr The input array
 * @param $key The key pointing to the desired value
 * @return The value mapped to $key or null if none
 */
function array_remove(array &$arr, $key) {
    if (array_key_exists($key, $arr)) {
        $val = $arr[$key];
        unset($arr[$key]);
        return $val;
    }
    return null;
}
