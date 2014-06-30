<?php
namespace API\Model;

/**
 * Base model
 */
class Model extends \DLModel
{
    protected $guarded = array();
    protected $primaryKey = '_id';

    public function freshTimestamp()
    {
        return time();
    }

    public function freshTimestampString()
    {
        return time();
    }

    public function getDates()
    {
        return array();
    }

}
