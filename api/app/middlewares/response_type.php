<?php

class ResponseTypeMiddleware extends \Slim\Middleware
{

	public function call()
	{
		// The Slim application
		$app = $this->app;

		// Respond based on ACCEPT request header
		if ($app->request->headers->get('ACCEPT') == 'text/event-stream') {

			// Set response headers
			$app->response->headers->set('Content-type', 'text/event-stream');
			foreach($app->response->headers as $header => $content) {
				header("{$header}: {$content}");
			}

			$pool_start = time();
			$last_event_id = $app->request->headers->get('Last-Event-ID');

			do {
				// Close EventSource connection after 4 seconds
				// let the client re-open it if necessary
				if ((time() - $pool_start) > 4) {
					die();
				}

				// Append last-event-id to filtering options
				if ($last_event_id) {
					$query_data = $this->decode_query_string();
					if (!isset($query_data['q'])) {
						$query_data['q'] = array();
					}
					array_push($query_data['q'], array('_id', '>', $last_event_id));
					$app->environment->offsetSet('slim.request.query_hash', $query_data);
				}

				$this->next->call();

				// Multiple results
				if (method_exists($app->content, 'each')) {
					$app->content->each(function($data) use ($app, &$last_event_id) {
						echo 'id: '. $data->_id . PHP_EOL;
						echo 'data: '. $data->toJson() . PHP_EOL;
						echo PHP_EOL;
						ob_flush();
						flush();
						$last_event_id = $data->_id;
					});

				} else {
					// Single result
					echo 'id: '. $app->content->_id . PHP_EOL;
					echo 'data: '. $app->content->toJson() . PHP_EOL;
					echo PHP_EOL;
					ob_flush();
					flush();
					$last_event_id = $data->content->_id;
				}

				sleep(2);
			} while (true);

		} else {
			// Call current request
			$this->next->call();

			$app->response->headers->set('Content-type', 'application/json');
			$app->response->setBody( $app->content->toJson() );
		}

	}

	public function decode_query_string() {
		// Parse incoming JSON QUERY_STRING
		// OBS: that's pretty much an uggly thing, but we need data types here.
		// Every param is string on query string (srsly?)
		$query_string = $this->app->environment->offsetGet('QUERY_STRING');
		$query_data = array();

		if (strlen($query_string)>0) {
			$query_data = json_decode(urldecode($query_string), true);
			$this->app->environment->offsetSet('slim.request.query_hash', $query_data);
		}

		return $query_data;
	}

}
