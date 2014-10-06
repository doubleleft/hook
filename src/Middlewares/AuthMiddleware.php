<?php
namespace Hook\Middlewares;

use Slim;

class AuthMiddleware extends Slim\Middleware
{

    public function call()
    {
        $app = $this->app;

        $request_path = $app->request->getResourceUri();

        // if (!$app->key && strpos($app->request->getPath(), "/apps/") === false) {
        // 	$app->response->setStatus(403);
        // 	$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
        // 	return;
        // }

        return $this->next->call();
    }

}
