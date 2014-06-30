<?php
namespace API\Model;

/**
 * RequestLog
 *
 * @see \Model\Model
 */
class RequestLog extends Model
{

    public function app()
    {
        return $this->belongsTo('Model\App');
    }

}
