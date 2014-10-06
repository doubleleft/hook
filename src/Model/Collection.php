<?php
namespace Hook\Model;

use Hook\Database\Schema as Schema;
use Hook\Database\Relationship as Relationship;

/**
 * Collections load & execute custom observers automatically.
 * They also can be retrieved by `App::collection('my_collection')`
 *
 * @uses DynamicModel
 */
class Collection extends DynamicModel
{
    protected $table = '_collections';

    protected static $observers;
    public static $lastTableName;

    const ATTACHED_FILES = 'attached_files';
    protected $_attached_files;

    protected $hidden = array('deleted_at');

    public static function boot()
    {
        parent::boot();

        if (!static::$observers) { static::$observers = array(); }
        if (!static::$booted) { static::$booted = array(); }
    }

    public static function loadObserver($table)
    {
        // Compile observer only if it isn't compiled yet.
        if (!isset(static::$observers[ $table ])) {
            // Register default events (DynamicModel)
            static::registerDefaultEvents($table);

            if ($module = Module::observer($table)) {
                $observer = $module->compile();
                static::$observers[ $table ] = $observer;
                static::observe($observer);
            } else {
                // Cache observer as not available
                static::$observers[ $table ] = false;
            }
        }
    }

    /**
     * getObserver
     * @param string $name
     * @return Class | null
     */
    public static function getObserver($table) {
        return (isset(static::$observers[$table]) && static::$observers[$table]) ? static::$observers[$table] : null;
    }

    /**
     * from
     * @param  string                            $table table
     * @return Illuminate\Database\Query\Builder
     */
    public static function from($table)
    {
        static::$lastTableName = $table;
        static::loadObserver($table);

        return static::query()->from($table);
    }

    public function __construct(array $attributes = array())
    {
        if (isset($attributes['table_name'])) {
            static::$lastTableName = $attributes['table_name'];
            $this->setTable(static::$lastTableName);
            unset($attributes['table_name']);
        } elseif (static::$lastTableName) {
            $this->setTable(static::$lastTableName);
        }

        // Configure date fields to output /
        $table_name = $this->getTable();
        $schema = Schema\Cache::get($table_name);
        if ($schema && isset($schema['attributes'])) {
            foreach($schema['attributes'] as $attribute) {
                if (isset($attribute['type']) && $attribute['type'] == 'timestamp') {
                    array_push($this->dates, $attribute['name']);
                }
            }
        }

        parent::__construct($attributes);

        static::loadObserver($table_name);
    }

    /**
     * isModified
     * @return bool
     */
    public function isModified()
    {
        return (count($this->getDirty()) > 0 || $this->hasAttachedFiles());
    }

    /**
     * hasAttachedFiles
     * @return bool
     */
    public function hasAttachedFiles()
    {
        return count($this->_attached_files) > 0;
    }

    /**
     * toArray. Modules may define a custom toArray method.
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $observer = static::getObserver($this->getTable());

        if ($observer) {
            if (method_exists($observer, 'toArray')) {
                return $observer->toArray($this, $array);
            }
        }

        return $array;
    }

    public function setAttachedFilesAttribute($files)
    {
        $this->_attached_files = $files;
    }

    protected function uploadAttachedFiles()
    {
        foreach ($this->_attached_files as $field => $file) {
            $_file = File::create(array('file' => $file));
            $this->setAttribute($field, $_file->path);
            $this->setAttribute($field . '_id', $_file->_id);
        }
        $this->_attached_files = null;
    }

    //
    // Hooks
    //

    public function beforeSave()
    {
        // Upload/relate each file attachment on the collection.
        if ($this->hasAttachedFiles()) {
            $this->uploadAttachedFiles();
        }

        return parent::beforeSave();
    }

    //
    // Protected methods - event fire/register
    //

    //
    // Use $lastTableName instead of get_class to register events on Collections
    //
    // @override
    // http://laravel.com/api/source-class-Illuminate.Database.Eloquent.Model.html#_registerModelEvent
    //
    protected static function registerModelEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::$lastTableName;
            static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
        }
    }

    //
    // Use $lastTableName instead of get_class to register events on Collections
    //
    // @override
    // http://laravel.com/api/source-class-Illuminate.Database.Eloquent.Model.html#_fireModelEvent
    //
    protected function fireModelEvent($event, $halt = true)
    {
        if ( ! isset(static::$dispatcher)) return true;

        // We will append the names of the class to the event to distinguish it from
        // other model events that are fired, allowing us to listen on each model
        // event set individually instead of catching event for all the models.
        $event = "eloquent.{$event}: " . $this->getTable();

        $method = $halt ? 'until' : 'fire';

        return static::$dispatcher->$method($event, $this);
    }

}
