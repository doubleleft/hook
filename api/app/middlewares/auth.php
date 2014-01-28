<?php

class AuthMiddleware extends \Slim\Middleware
{

	public function call()
	{
		// The Slim application
		$app = $this->app;

		$referer = parse_url($app->environment->offsetGet('HTTP_REFERER'));

		// Enable Cross-Origin Resource Sharing
		$app->response->headers->set('Access-Control-Allow-Origin', (isset($referer['host'])) ? 'http://'.$referer['host'] : '*' );
		$app->response->headers->set('Access-Control-Allow-Credentials', 'true');
		$app->response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE');
		$app->response->headers->set('Access-Control-Allow-Headers', 'x-app-id, x-app-key, content-type, user-agent, accept');

		// Don't proceed on CORS requests.
		if (!$app->request->isOptions()) {
			$app->key = Models\AppKey::where('app_id', $app->request->headers->get('X-App-Id') ?: $app->request->get('X-App-Id'))
				->where('key', $app->request->headers->get('X-App-Key') ?: $app->request->get('X-App-Key'))
				->first();

			// if (!$app->key && strpos($app->request->getPath(), "/apps/") === false) {
			// 	$app->response->setStatus(403);
			// 	$app->response->setBody(json_encode(array('error' => "Invalid credentials.")));
			// 	return;
			// }

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
