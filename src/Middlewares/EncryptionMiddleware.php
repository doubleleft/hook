<?php
namespace Hook\Middlewares;

use Slim;

class EncryptionMiddleware extends Slim\Middleware
{

    public function call()
    {
        $app = $this->app;
        return $this->next->call();
    }

}
