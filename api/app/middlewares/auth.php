<?php

class AuthMiddleware extends \Slim\Middleware
{

	public function call()
	{
		$app = $this->app;

		$app->auth_token = \Model\AuthToken::current();

		$request_path = $app->request->getResourceUri();

		// if (!$app->key && strpos($app->request->getPath(), "/apps/") === false) {
		// 	$app->response->setStatus(403);
		// 	$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
		// 	return;
		// }

		$this->next->call();
	}

}

