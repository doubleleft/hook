<?php
namespace Model;

/**
 * KeyValue
 *
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
class KeyValue extends \Core\Model
{

	public static function upsert($data) {
		$key = self::where('app_id', $data['app_id'])
			->where('name', $data['name'])
			->first();

		if (!$key) { $key = new self($data); }

		$key->value = $data['value'];
		$key->save();

		return $key;
	}

}

