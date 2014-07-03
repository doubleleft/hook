<?php
namespace API\Model;

/**
 * AppConfig
 */
class AppConfig extends Model
{

    /**
     * Get app config value by name
     * @param string name
     * @param string default
     * @return string
     */
    public static function get($name, $default = null)
    {
        $config = static::where('name', $name)->first();

        return ($config) ? $config->value : $default;
    }

    /**
     * Get app configs by pattern
     * @param string pattern
     * @return Illuminate\Support\Collection
     */
    public static function getAll($pattern)
    {
        return static::where('name', 'like', $pattern)->get();
    }

}
