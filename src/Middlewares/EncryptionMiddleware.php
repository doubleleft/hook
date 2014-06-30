<?php
namespace API\Middlewares;

use Slim;

class EncryptionMiddleware extends Slim\Middleware
{

    public function call()
    {
        $app = $this->app;
        $this->next->call();
    }

}
