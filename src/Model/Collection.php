<?php
namespace Hook\Model;

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
            }
        }
    }

    /**
     * getObserver
     * @param string $name
     * @return Class | null
     */
    public static function getObserver($table) {
        return (isset(static::$observers[$table])) ? static::$observers[$table] : null;
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
        parent::__construct($attributes);
        static::loadObserver($this->getTable());
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

    /**
     * Drop the collection
     * @method drop
     */
    public function drop()
    {
        $conn = $this->getConnectionResolver()->connection();
        $builder = $conn->getSchemaBuilder();
        $builder->dropIfExists($this->getTable());

        return true;
    }

    public function setAttachedFilesAttribute($files)
    {
        $this->_attached_files = $files;
    }

    protected function uploadAttachedFiles($files)
    {
        foreach ($files as $field => $file) {
            $_file = File::create(array('file' => $file));
            $this->setAttribute($field, $_file->path);
            $this->setAttribute($field . '_id', $_file->_id);
        }
    }

    //
    // Hooks
    //

    public function beforeSave()
    {
        // Upload/relate each file attachment on the collection.
        if ($this->_attached_files) {
            $this->uploadAttachedFiles($this->_attached_files);
            $this->_attached_files = null;
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
        $event = "eloquent.{$event}: ".static::$lastTableName;

        $method = $halt ? 'until' : 'fire';

        return static::$dispatcher->$method($event, $this);
    }

}
