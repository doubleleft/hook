<?php

class AuthMiddleware extends \Slim\Middleware
{

	public function call()
	{
		$app = $this->app;

		$app->auth_token = \models\AuthToken::where('token', $app->request->headers->get('X-Auth-Token') ?: $app->request->get('X-Auth-Token'))
			->where('expire_at', '>=', time())
			->first();

		$request_path = $app->request->getResourceUri();

		// if (!$app->key && strpos($app->request->getPath(), "/apps/") === false) {
		// 	$app->response->setStatus(403);
		// 	$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
		// 	return;
		// }

		$this->next->call();
	}

}

