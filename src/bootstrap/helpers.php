<?php

use Hook\Model\App;
use Hook\Http\Router;

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

function unparse_url($parsed_url) {
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass = ($user || $pass) ? "$pass@" : '';
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}

function to_json($data) {
    if (is_string($data)) {
        $json = $data;
    } else if (method_exists($data, 'toJson')) {
        $json = $data->toJson();
    } else {
        $json = json_encode($data);
    }
    return $json;
}

/**
 * debug - DEPRECATED, use Hook\Logger\Logger::debug
 * @param mixed $data
 */
function debug($data)
{
    return Hook\Logger\Logger::debug($data);
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

    $paths = Router::config('paths');
    return ($relative ? $paths['root'] . 'public/' : '') . $paths['storage'] . '/' . $app_id . '/';
}

/**
 * shared_storage_dir
 *
 * @param mixed $relative
 * @param mixed $app_id
 */
function shared_storage_dir()
{
    $paths = Router::config('paths');
    return $paths['shared_storage'];
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
 * rmdir_r - Remove a directory and all it's contents.
 *
 * @param mixed $dir
 */
function rmdir_r($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? rmdir_r("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * App shortchut functions
 */

/**
 * collection
 * @return string
 */
function collection($name)
{
    return App::collection($name);
}

/**
 * String functions
 */
if (!function_exists('str_slug'))
{
    function str_slug($title, $separator = '-') {
        // requires "patchwork/utf8"
        return \Illuminate\Support\Str::slug($title, $separator);
    }
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

/**
 * HTML functions
 */

/**
 * html_attributes
 *
 * @param array $attributes
 * @return string
 */
function html_attributes($attributes) {
    $tag_attributes = "";
    foreach ($attributes as $key => $value) {
        $tag_attributes .= ' ' . $key . '="' . $value . '"';
    }
    return $tag_attributes;
}
