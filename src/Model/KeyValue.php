<?php
namespace Hook\Model;

/**
 * KeyValue
 */
class KeyValue extends Model
{

    public static function upsert($data)
    {
        $key = self::where('name', $data['name'])->first();

        if (!$key) { $key = new self($data); }

        $key->value = $data['value'];
        $key->save();

        return $key;
    }

}
