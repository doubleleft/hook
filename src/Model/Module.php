<?php
namespace Hook\Model;

use Slim;

use Hook\Exceptions;

/**
 * Module
 *
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
class Module extends Model
{
    const TYPE_TEMPLATE = 'templates';
    const TYPE_OBSERVER = 'observers';
    const TYPE_ROUTE = 'routes';
    const TYPE_CHANNEL = 'channels';

    /**
     * Get a route module instance
     * @param string name
     * @return Module
     */
    public static function route($name)
    {
        return static::get(self::TYPE_ROUTE, $name.'.php');
    }

    /**
     * Get a channel module instance
     * @param string name
     * @return Module
     */
    public static function channel($name)
    {
        return static::get(self::TYPE_CHANNEL, $name.'.php');
    }

    /**
     * Get a observer module instance
     * @param string name
     * @return Module
     */
    public static function observer($name)
    {
        return static::get(self::TYPE_OBSERVER, $name.'.php');
    }

    /**
     * Get a template module instance, trying to fallback to a general template
     * @param string name
     * @return Module
     */
    public static function template($name)
    {
        $template = null;

        //
        // It's a template name or template contents?
        //
        if (preg_match('/^[A-Za-z0-9_\.\-\/]+\.html$/', $name)) {
            // It's a template name!
            $template = static::get(self::TYPE_TEMPLATE, $name);

            if (!$template) {
                $fallback_template_path = __DIR__ . '/../../storage/default/templates/' . $name;
                // try to retrieve local fallback template
                if (!file_exists($fallback_template_path)) {
                    throw new Exceptions\MethodFailureException("Template not found: '{$name}'. Please run `dl-api generate:template {$name}` to generate one.");
                }

                // use local template if can't find
                $template = new static(array(
                    'type' => self::TYPE_TEMPLATE,
                    'code' => file_get_contents($fallback_template_path),
                    'name' => $name
                ));
            }
        }

        if (!$template) {
            $template = new static(array(
                'type' => self::TYPE_TEMPLATE,
                'code' => $name,
                'name' => 'inline-template.html'
            ));
        }

        return $template;
    }

    /**
     * Get a module instance
     * @param string type
     * @param string name
     * @return Module
     */
    public static function get($type, $name)
    {
        return static::where('type', $type)->where('name', $name)->first();
    }

    /**
     * dump
     * @return array
     */
    public static function dump()
    {
        $modules = array();
        static::select('name', 'type', 'updated_at')->get()->each(function($module) use (&$modules) {
            if (!isset($modules[ $module->type ])) {
                $modules[ $module->type ] = array();
            }
            $modules[ $module->type ][ $module->name ] = $module->updated_at->timestamp;
        });
        return $modules;
    }

    /**
     * deploy
     *
     * @param mixed $module
     * @return bool
     */
    public static function deploy($sync)
    {
        $stats = array(
            'removed' => 0,
            'updated' => 0,
            'uploaded' => 0
        );

        if (!isset($sync['upload'])) { $sync['upload'] = array(); }
        if (!isset($sync['update'])) { $sync['update'] = array(); }
        if (!isset($sync['remove'])) { $sync['remove'] = array(); }

        // remove requested modules
        if (isset($sync['remove'])) {
            foreach(array_keys($sync['remove']) as $type) {
                $stats['removed'] += 1;
                static::where('type', $type)->whereIn('name', $sync['remove'][$type])->delete();
            }
        }

        $updates = array_merge_recursive($sync['update'], $sync['upload']);
        foreach($updates as $type => $modules) {
            foreach($modules as $name => $module) {
                $code = $module[0];
                $updated_at = $module[1];

                $_module = static::firstOrNew(array(
                    'type' => $type,
                    'name' => $name
                ));

                $stats[ (($_module->_id) ? 'updated' : 'created') ] += 1;

                $_module->code = $code;
                $_module->updated_at = $updated_at;
                $_module->save();
            }
        }

        return $stats;
    }

    /**
     * compile
     * Compile module code
     * @param array options
     * @return mixed
     */
    public function compile($options=array())
    {
        $extension = '.' . pathinfo($this->name, PATHINFO_EXTENSION);
        $name = basename($this->name, $extension);

        if ($extension === ".php") {
            //
            // Expose handy aliases for modules
            //
            $aliases = 'use Hook\Mailer\Mail as Mail;';
            $aliases.= 'use Hook\Model\App as App;';
            $aliases.= 'use Hook\Model\AppConfig as AppConfig;';
            $aliases.= 'use Hook\Model\Module as Module;';
            $aliases.= 'use Hook\Model\File as File;';
            $aliases.= 'use Hook\Model\Auth as Auth;';
            $aliases.= 'use Hook\Model\AuthToken as AuthToken;';
            $aliases.= 'use Hook\Model\Collection as Collection;';
            $aliases.= 'use Hook\Cache\Cache as Cache;';

            if ($this->type == self::TYPE_OBSERVER || $this->type == self::TYPE_CHANNEL) {
                // Prevent name conflict by using unique class names for custom modules
                $klass = 'CustomModule' . uniqid();
                eval($aliases . preg_replace('/class ([^\ {]+)/', 'class ' . $klass, $this->code, 1));

                if (class_exists($klass)) {
                    // Return module instance for registering on model.
                    return new $klass;
                } else {
                    throw new Exceptions\MethodFailureException("Module '{$name}.php' must define a class.");
                }

            } elseif ($this->type == self::TYPE_ROUTE) {
                $app = Slim\Slim::getInstance();
                try {
                    eval($aliases . $this->code);
                } catch (\Exception $e) {
                    $message = $this->name . ': ' . $e->getMessage();
                    $app->log->info($message);
                    $app->response->headers->set('X-Error-'.uniqid(), $message);
                    file_put_contents('php://stderr', $message);
                }
            }

        } elseif ($extension === '.html') {
            $template = $this->code;

            // always use array for options
            if (gettype($options)==='object') {
                $options = $options->toArray();
            }

            foreach ($options as $field => $value) {
                //
                // Please consider migrating it to mustache, for more complex templates:
                // https://github.com/bobthecow/mustache.php
                //
                if (gettype($value)==="object") { continue; }
                $template = preg_replace('/{{'.$field.'}}/', $value, $template);
            }

            return $template;
        }

    }

    public function getCodeAttribute()
    {
        $extension = pathinfo($this->name, PATHINFO_EXTENSION);
        $code = $this->attributes['code'];

        return ($extension==="php") ? substr($code, 5) : $code;
    }

}
