<?php namespace Hook\Controllers;

use Hook\Model\Auth;
use Hook\Auth\Provider as AuthProvider;

class AuthController extends HookController {
    public function show() {
        return $this->json(Auth::current());
    }

    public function execute($provider_name, $method = 'register') {
        return $this->json(AuthProvider::get($provider_name)->{$method}(CollectionController::getData()));
    }
}

