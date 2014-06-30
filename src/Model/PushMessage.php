<?php
namespace API\Model;

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
        static::creating(function ($model) { $model->beforeCreate(); });
    }

    public function app()
    {
        return $this->belongsTo('Model\App');
    }

    public function beforeCreate()
    {
        if (!AuthToken::current()) {
            // throw new \Exception("auth token is required to create push_messages.");
        }

        if (!$this->getAttribute('message')) {
            throw new \Exception("Can't create PushMessage: 'message' is required.");
        }

        $this->setAttribute('status', self::STATUS_QUEUE);
        $this->setAttribute('devices', 0);
        $this->setAttribute('failure', 0);
        $this->beforeSave();
    }

}
