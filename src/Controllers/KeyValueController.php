<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Http\Input;

class KeyValueController extends HookController {

    public function show($name) {
        $key = Model\KeyValue::where('name', $name)->first();
        return $this->json(($key) ? json_encode(unserialize($key->value)) : null);
    }

    public function store($name) {
        $value = Input::get('value');
        Model\KeyValue::upsert(array(
            'name' => $name,
            'value' => serialize($value)
        ));
        return $this->json(json_encode($value));
    }

    public function delete($name) {
        return $this->json(Model\KeyValue::where('name', $name)->delete());
    }

}
