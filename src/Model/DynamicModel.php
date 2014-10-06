<?php
namespace Hook\Model;

use Hook\Database\Schema as Schema;
use Hook\Database\Relationship as Relationship;
use Hook\Exceptions\BadRequestException as BadRequestException;

/**
 * Models that extends DynamicModel will have it's defined on-the-fly.
 * @uses Hook\Model\Model
 */
class DynamicModel extends Model
{
    protected static $booted = array();

    protected $guarded = array('created_at');

    protected $observables = array('updating_multiple', 'deleting_multiple');
    protected $softDelete = true;

    protected $relatedFields;

    protected static function registerDefaultEvents($table=null)
    {
        if (is_null($table)) {
            // register events using class name
            static::saving(function ($model) { $model->beforeSave(); });
            static::saved(function ($model) { $model->afterSave(); });
            static::creating(function ($model) { $model->beforeCreate(); });

        } else if (!isset(static::$booted[ $table ])) {

            // register events using table name
            static::$booted[ $table ] = true;
            static::saving(function ($model) { $model->beforeSave(); });
            static::saved(function ($model) { $model->afterSave(); });
            static::creating(function ($model) { $model->beforeCreate(); });
        }
    }

    /**
     * isModified
     * @return bool
     */
    public function isModified()
    {
        return count($this->getDirty()) > 0;
    }

    //
    // Hooks
    //

    public function beforeCreate() { }

    public function beforeSave()
    {
        // create or associate nested values as relationships
        $this->relatedFields = $this->extractRelatedFields($this->attributes);

        // create beforeSave related objects
        $this->createRelatedFields('beforeSave');

        Schema\Builder::dynamic($this, $this->attributes);
    }

    public function afterSave()
    {
        // create afterSave related objects
        $this->createRelatedFields('afterSave');
    }

    protected function createRelatedFields($type)
    {
        $fields = $this->relatedFields[$type];

        foreach($fields as $field => $objects) {
            $relationship = Relationship::getRelation($this, $field);

            if (count($objects) == count($objects, COUNT_RECURSIVE))
            {
                $objects = array($objects);
            }

            foreach($objects as $object) {
                // force $this._id on afterSave relationships
                if ($type == 'afterSave') {
                    $object[$relationship->getPlainForeignKey()] = $this->_id;
                }

                // associate related item _id to the model
                $this->attributes[$field . '_id'] = (isset($object['_id']))
                    ? $object['_id']
                    : $relationship->getRelated()->create($object)->_id;

            }
        }

    }

    protected function extractRelatedFields(&$attributes)
    {
        $relatedFields = array(
            // relationships that need to be saved on this model
            'beforeSave' => array(),

            // relationships that have '_id' reference on other table
            'afterSave' => array()
        );

        foreach($attributes as $field => $values) {
            // Model\Collection found, use it's data as array
            if (is_object($values) && method_exists($values, 'toArray')) {
                $values = $values->toArray();
            }

            // nested values found
            if (is_array($values)) {
                // does a relationship with this name exists?
                $relationship = Relationship::getRelation($this, $field);

                if (!is_null($relationship)) {
                    // remove embedded field to insert into it's respective relationship
                    unset($attributes[$field]);

                    if (preg_match('/\./', $relationship->getForeignKey())) {
                        $relatedFields['afterSave'][$field] = $values;
                    } else {
                        $relatedFields['beforeSave'][$field] = $values;
                    }


                } else if (Schema\Builder::isSupported()) {
                    // no relationship with field name found.
                    // probably it's a programming issue. throw an exception.
                    throw new BadRequestException("nested objects not supported.");
                }

            }
        }

        return $relatedFields;
    }

    public function __call($method, $parameters)
    {
        // get requested relationship, or use parent function
        return Relationship::getRelation($this, $method)
            ?: parent::__call($method, $parameters);
    }


}
