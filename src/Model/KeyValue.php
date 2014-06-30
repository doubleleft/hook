<?php
namespace API\Model;

/**
 * KeyValue
 */
class KeyValue extends Model
{

    public static function upsert($data)
    {
        $key = self::where('app_id', $data['app_id'])
            ->where('name', $data['name'])
            ->first();

        if (!$key) { $key = new self($data); }

        $key->value = $data['value'];
        $key->save();

        return $key;
    }

}
