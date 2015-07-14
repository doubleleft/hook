<?php
namespace Hook\Model;

use DateTime;

/**
 * Base model
 */
class Model extends \DLModel
{
    protected $guarded = array();
    protected $primaryKey = '_id';

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @override serializeDate
     * @param  \DateTime  $date
     * @return string
     */
    protected function serializeDate(DateTime $date)
    {
        //
        // Use a string representing an RFC2822 or ISO 8601 date
        // http://tools.ietf.org/html/rfc2822#page-14
        //
        return $date->format('Y-m-d\TH:i:sP');
    }
}
