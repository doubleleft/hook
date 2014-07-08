<?php
namespace Hook\Model;

/**
 * Base model
 */
class Model extends \DLModel
{
    protected $guarded = array();
    protected $primaryKey = '_id';
}
