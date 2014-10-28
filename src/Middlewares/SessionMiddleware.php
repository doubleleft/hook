<?php namespace Hook\Middlewares;

use Hook\Application\Context;
use Slim\Middleware\SessionCookie;

class SessionMiddleware extends SessionCookie
{
    public function __construct($settings = array())
    {
        parent::__construct(array(
            'name' => 'hook_session'
        ));
    }

    public function call() {
        // $this->app->config('cookies.secret_key', Context::getKey()->app->secret);
        return parent::call();
    }

}
