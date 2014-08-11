<?php namespace Hook\Controllers;

use Hook\Http\Router;
use Closure;

class HookController {

    protected function before(Closure $callback) {
        Router::getInstance()->hook('slim.before.dispatch', $callback);
    }

    protected function after(Closure $callback) {
        Router::getInstance()->hook('slim.after.dispatch', $callback);
    }

    protected function json($data) {
        $response = Router::getInstance()->response;
        $response->headers->set('Content-type', 'application/json');
        $response->setBody(to_json($data));
    }

}

