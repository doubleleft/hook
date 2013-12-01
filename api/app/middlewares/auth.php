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
		if (!$app->request->isOptions()) {
			$app->key = Models\AppKey::where('app_id', $app->request->headers->get('X-App-Id'))
				->where('key', $app->request->headers->get('X-App-Key'))
				->first();

			$app->response->headers->set('Content-type', 'application/json');

			// if (!$app->key && strpos($app->request->getPath(), "/apps/") === false) {
			// 	$app->response->setStatus(403);
			// 	$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
			// 	return;
			// }

			//
			// Parse incoming JSON QUERY_STRING
			// OBS: that's pretty much an uggly thing, but we need data types here.
			// Every param is string on query string (srsly?)
			$query_string = $app->environment->offsetGet('QUERY_STRING');
			if (strlen($query_string)>0) {
				$app->environment->offsetSet('slim.request.query_hash', json_decode(urldecode($query_string), true));
			}

			//
			// Parse incoming JSON data
			if ($app->request->isPost() || $app->request->isPut()) {
				$input_data = file_get_contents('php://input');
				$app->environment->offsetSet('slim.request.form_hash', json_decode($input_data, true));
			}

			$this->next->call();
		}
	}

}
