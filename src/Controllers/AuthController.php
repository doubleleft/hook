<?php namespace Hook\Controllers;

use Hook\Http\Router;
use Hook\Database\AppContext;

use Hook\Model\Auth;
use Hook\Auth\Provider as AuthProvider;

use Opauth;

class AuthController extends HookController {

    public function show() {
        return $this->json(Auth::current());
    }

    public function execute($provider_name, $method = 'register') {
        return $this->json(AuthProvider::get($provider_name)->{$method}(CollectionController::getData()));
    }

    public function oauth($method=null, $callback=null) {
        $strategies = array(
            'Twitter' => array(
                'key' => 'JehPM5P4UxbVbrEQlrtx6ED2x',
                'secret' => 'M19cCQwfWvA3vBVsNTULruD9Ez5PzJf0GPpWe2YF7DzQxvEkYU',
                'oauth_callback' => '{complete_url_to_strategy}oauth_callback?' . http_build_query(array(
                    'X-App-Id' => $_GET['X-App-Id'],
                    'X-App-Key' => $_GET['X-App-Key'],
                )),
            ),
        );

        if (isset($_POST['opauth'])) {
            var_dump(unserialize(base64_decode( $_POST['opauth'] )));
            die();
        }

        $opauth = new Opauth(array(
            'path' => substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'oauth/') + 6),
            // 'callback_url' => '{path}callback',
            'callback_url' => '{path}callback?' . http_build_query(array(
                'X-App-Id' => $_GET['X-App-Id'],
                'X-App-Key' => $_GET['X-App-Key'],
            )),
            'callback_transport' => 'post',
            'Strategy' => $strategies,
            'security_salt' => "shhhhhhh, it's a secret",
            // 'debug' => true,
        )); // don't auto-run

    }

}

