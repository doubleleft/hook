<?php namespace Hook\Controllers;

use Hook\Http\Router;

class HookController {

    protected function json($data) {
        Router::getInstance()->response->setBody(to_json($data));
    }

    protected function view($name, $data) {
    }

}

