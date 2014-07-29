<?php namespace Hook\Controllers;

class KeyValueController extends HookController {
    public function show($key) {
        $key = Model\KeyValue::where('name', $name)->first();
        return Response::json(($key) ? $key->value : null);
    }

    public function store($key) {
        return Model\KeyValue::upsert(array(
            'name' => $name,
            'value' => Input::get('value')
        ))->value;
    }
}
