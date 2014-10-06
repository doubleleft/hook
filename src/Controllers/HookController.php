<?php namespace Hook\Controllers;

use Hook\Http\Router;
use Closure;

class HookController {
    protected $view;

    public function __construct() {
        $this->view = Router::getInstance()->view;

        if (method_exists($this, 'before')) {
            $this->before();
            // Router::getInstance()->hook('slim.before.dispatch', $callback);
        }

        if (method_exists($this, 'after')) {
            Router::getInstance()->hook('slim.after.dispatch', $callback);
        }
    }

    /**
     * json
     *
     * @param mixed $data
     */
    protected function json($data) {
        $response = Router::getInstance()->response;
        $response->headers->set('Content-type', 'application/json');
        $response->setBody(to_json($data));
    }

    /**
     * view
     *
     * @param mixed $template
     * @param array $data
     */
    protected function render($template, $data = array()) {
        Router::getInstance()->render($template, $data);
    }

}

