<?php
namespace Hook\Model;

/**
 * Base model
 */
class Model extends \DLModel
{
    protected $guarded = array();
    protected $primaryKey = '_id';

    //
    // Setup default date format
    // Use a string representing an RFC2822 or ISO 8601 date
    // http://tools.ietf.org/html/rfc2822#page-14
    //
    protected $dateFormat = 'Y-m-d\TH:i:sP';

}
