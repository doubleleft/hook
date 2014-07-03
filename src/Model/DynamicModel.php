<?php
namespace API\Model;

use API\Database\Schema as Schema;

/**
 * Models that extends DynamicModel will have it's defined on-the-fly.
 * @uses API\Model\Model
 */
class DynamicModel extends Model
{
    protected static $booted = array();
    protected $observables = array('updating_multiple', 'deleting_multiple');

    protected $softDelete = true;

    protected static function registerDefaultEvents($table=null)
    {
        if (is_null($table)) {
            static::saving(function ($model) { $model->beforeSave(); });

        } else if (!isset(static::$booted[ $table ])) {
            static::$booted[ $table ] = true;
            static::saving(function ($model) { $model->beforeSave(); });
        }
    }

    public function beforeSave()
    {
        Schema\Builder::dynamic($this, $this->attributes);
    }

}
