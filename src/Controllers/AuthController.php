<?php namespace Hook\Controllers;

use Hook\Model\Auth;
use Hook\Auth\Provider as AuthProvider;

class AuthController extends HookController {

    public function show() {
        return Auth::current();
    }

    public function execute($provider_name, $method = 'register') {
        return AuthProvider::get($provider_name)->{$method}(CollectionController::getData());
    }

}

