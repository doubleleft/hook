<?php
namespace Hook\Model;

use Hook\Database\AppContext as AppContext;
use Hook\Exceptions\ForbiddenException as ForbiddenException;
use Hook\Exceptions\InternalException as InternalException;


/**
 * Messages to be delievered to devices.
 *
 * @see DynamicModel
 */
class PushMessage extends DynamicModel
{
    protected $table = 'push_messages';

    const STATUS_QUEUE = 0;
    const STATUS_ERROR = 1;
    const STATUS_SENDING = 2;
    const STATUS_SENT = 3;

    public static function boot()
    {
        parent::boot();
        static::registerDefaultEvents();
    }

    public function beforeSave()
    {
        if (!$this->getAttribute('_id')) {

            if (!AppContext::getKey()->isServer()) {
                throw new ForbiddenException("Need a 'server' key to perform this action.");
            }

            if (!$this->getAttribute('message')) {
                throw new InternalException("Can't create PushMessage: 'message' is required.");
            }

            $this->setAttribute('status', self::STATUS_QUEUE);
            $this->setAttribute('devices', 0);
            $this->setAttribute('failure', 0);
        }

        parent::beforeSave();
    }

}
