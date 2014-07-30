<?php namespace Hook\Controllers;

class AuthController extends HookController {
    public function show() {
        return $this->json(Model\Auth::current());
    }

    public function execute($provider_name, $method = 'register') {
        return $this->json(Auth\Provider::get($provider_name)->{$method}(CollectionController::getData()));
    }
}

