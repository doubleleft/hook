<?php
namespace API\Model;

/**
 * AppConfig
 */
class AppConfig extends Model
{
    public function app()
    {
        return $this->belongsTo('Model\App');
    }

    /**
     * Get app config value by name
     * @param string name
     * @param string default
     * @return string
     */
    public static function get($name, $default = null)
    {
        $config = static::current()->where('name', $name)->first();

        return ($config) ? $config->value : $default;
    }

    /**
     * Get app configs by pattern
     * @param string pattern
     * @return Illuminate\Support\Collection
     */
    public static function getAll($pattern)
    {
        return static::current()->where('name', 'like', $pattern)->get();
    }

    /**
     * Current app scope
     * @example
     *     AppConfig::current()->where('name', 'like', 'mail.%')->get()
     */
    public function scopeCurrent($query)
    {
        return $query->where('app_id', App::currentId());
    }

}
