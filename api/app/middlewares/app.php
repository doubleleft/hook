<?php

class AppMiddleware extends \Slim\Middleware
{

	public static function decode_query_string() {
		$app = Slim\Slim::getInstance();

		// Parse incoming JSON QUERY_STRING
		// OBS: that's pretty much an uggly thing, but we need data types here.
		// Every param is string on query string (srsly?)
		$query_string = $app->environment->offsetGet('QUERY_STRING');
		$query_data = array();

		if (strlen($query_string)>0) {
			$query_data = json_decode(urldecode($query_string), true);
			$app->environment->offsetSet('slim.request.query_hash', $query_data);
		}

		return $query_data;
	}

	public function call()
	{
		// The Slim application
		$app = $this->app;

		$referer = parse_url($app->environment->offsetGet('HTTP_REFERER'));

		// Enable Cross-Origin Resource Sharing
		$app->response->headers->set('Access-Control-Allow-Origin', (isset($referer['host'])) ? 'http://'.$referer['host'] : '*' );
		$app->response->headers->set('Access-Control-Allow-Credentials', 'true');
		$app->response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE');
		$app->response->headers->set('Access-Control-Allow-Headers', 'x-app-id, x-app-key, x-auth-token, content-type, user-agent, accept');

		self::decode_query_string();

		// Don't proceed on CORS requests.
		if (!$app->request->isOptions()) {
			$app->key = models\AppKey::where('app_id', $app->request->headers->get('X-App-Id') ?: $app->request->get('X-App-Id'))
				->where('key', $app->request->headers->get('X-App-Key') ?: $app->request->get('X-App-Key'))
				->first();

			if ($app->key) {
				if ($custom_routes = models\Module::currentApp()->where('type', models\Module::TYPE_ROUTE)->get()) {
					foreach($custom_routes as $custom_route) {
						$custom_route->compile();
					}
				}
			} else if (!preg_match('/apps/', $app->request->getResourceUri())) {
				// throw new ForbiddenException("invalid application credentials");
			}

			//
			// Parse incoming JSON data
			if ($app->request->isPost() || $app->request->isPut()) {
				$input_data = $app->environment->offsetGet('slim.input');
				$app->environment->offsetSet('slim.request.form_hash', json_decode($input_data, true));
			}

			$this->next->call();
		}
	}

}
