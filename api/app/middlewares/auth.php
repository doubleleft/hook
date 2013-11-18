<?php

class AuthMiddleware extends \Slim\Middleware
{

	public function call()
	{
		// The Slim application
		$app = $this->app;

		// Enable Cross-Origin Resource Sharing
		$app->response->headers->set('Access-Control-Allow-Origin', '*');
		$app->response->headers->set('Access-Control-Allow-Method', 'GET, PUT, POST, DELETE');
		$app->response->headers->set('Access-Control-Allow-Headers', 'x-app-id, x-app-key, content-type, user-agent');

		// Don't proceed on CORS requests.
		if ($app->request->getMethod() != "OPTIONS") {
			$app->key = Models\AppKey::where('app_id', $app->request->headers->get('X-App-Id'))
				->where('key', $app->request->headers->get('X-App-Key'))
				->first();

			$app->response->headers->set('Content-type', 'application/json');

			if (!$app->key) {
				$app->response->setStatus(403);
				$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
			}

			// Parse incoming JSON data
			if ($input_data = file_get_contents('php://input')) {
				$_POST = json_decode($input_data, true);
			}

			$this->next->call();
		}
	}

}
