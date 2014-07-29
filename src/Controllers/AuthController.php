<?php namespace Hook\Controllers;

class AuthController extends HookController {
    public function show() {
        return Response::json(Model\Auth::current());
    }

    public function execute($provider_name, $method = 'register') {
        return Response::json(Auth\Provider::get($provider_name)->{$method}(CollectionController::getData()));
    }
}

