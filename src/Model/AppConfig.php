<?php
namespace Hook\Model;

/**
 * AppConfig
 */
class AppConfig extends Model
{

    public static function deploy($value, $key = array())
    {
        if (is_array($value)) {
            foreach ($value as $name => $config) {
                $path = $key;
                $path[] = $name;
                self::deploy($config, $path);
            }
        } else {
            $config = self::firstOrNew(array('name' => join('.', $key)));
            $config->value = $value;
            $config->updated_at = \Carbon\Carbon::now()->addSeconds(3);
            $config->save();
        }
    }

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
    public static function getAll($pattern, $default = null)
    {
        $configs = static::select('value')->where('name', 'like', $pattern)->get()->map(function($config) {
            return $config->value;
        })->toArray();

        return (empty($configs) && $default) ? $default : $configs;
    }

}
