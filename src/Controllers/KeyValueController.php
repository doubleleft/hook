<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Http\Input;

class KeyValueController extends HookController {

    public function show($name) {
        $key = Model\KeyValue::where('name', $name)->first();
        return $this->json(($key) ? $key->value : null);
    }

    public function store($name) {
        return $this->json(Model\KeyValue::upsert(array(
            'name' => $name,
            'value' => Input::get('value')
        ))->value);
    }

}
