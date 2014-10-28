<?php namespace Hook\Application;

use Hook\Application\Core\DotNotation;

class Config
{
    private static $instance;

    public static function getInstance() {
        if (!static::$instance) {
            $config_file = static::getConfigPath();
            $configs = (file_exists($config_file)) ? require($config_file) : array();
            static::$instance = new DotNotation($configs);
        }
        return static::$instance;
    }

    public static function getConfigPath() {
        return storage_dir() . 'config.php';
    }

    public static function deploy($configs = array()) {
        $config_file = static::getConfigPath();
        $previous = file_exists($config_file) ? require($config_file) : array();
        if ($configs != $previous) {
            file_put_contents($config_file, '<?php return ' .var_export($configs, true) . ';');
        }
    }

    public static function __callStatic($method, $arguments) {
        return call_user_func_array(array(static::getInstance(), $method), $arguments);
    }

}
