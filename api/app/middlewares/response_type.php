<?php

class ResponseTypeMiddleware extends \Slim\Middleware
{
	const MAX_REFRESH_TIMEOUT = 40; // 40 seconds
	const POOLING_DEFAULT_RETRY = 10; // 10 seconds

	public function call()
	{
		// The Slim application
		$app = $this->app;

		// Respond based on ACCEPT request header
		if ($app->request->headers->get('ACCEPT') == 'text/event-stream') {

			$pool_start = time();
			$refresh_timeout = intval($app->request->get('refresh'));
			if ($refresh_timeout > self::MAX_REFRESH_TIMEOUT) {
				$refresh_timeout = self::MAX_REFRESH_TIMEOUT;
			}
			$retry_timeout = intval($app->request->get('retry', self::POOLING_DEFAULT_RETRY)) * 1000;
			$last_event_id = $app->request->headers->get('Last-Event-ID');

			echo 'retry: '. $retry_timeout . PHP_EOL;
			do {

				// Set response headers
				$app->response->headers->set('Content-type', 'text/event-stream');
				foreach($app->response->headers as $header => $content) {
					header("{$header}: {$content}");
				}

				// Close EventSource connection after 4 seconds
				// let the client re-open it if necessary
				if ((time() - $pool_start) > 15) {
					die();
				}

				// Append last-event-id to filtering options
				if ($last_event_id) {
					$query_data = AuthMiddleware::decode_query_string();
					if (!isset($query_data['q'])) {
						$query_data['q'] = array();
					}
					array_push($query_data['q'], array('_id', '>', $last_event_id));
					$app->environment->offsetSet('slim.request.query_hash', $query_data);
				}

				try {
					// Call current request
					$this->next->call();
				} catch (Exception $e) {
					$app->content = array('error' => $e->getMessage());
				}

				// Multiple results
				if (method_exists($app->content, 'each')) {
					$app->content->each(function($data) use ($app, &$last_event_id) {
						echo 'id: '. $data->_id . PHP_EOL;
						echo 'data: '. $this->encode_content($app->content) . PHP_EOL;
						echo PHP_EOL;
						ob_flush();
						flush();
						$last_event_id = $data->_id;
					});

				} else {
					// Single result
					echo 'id: '. $app->content->_id . PHP_EOL;
					echo 'data: '. $this->encode_content($app->content) . PHP_EOL;
					echo PHP_EOL;
					ob_flush();
					flush();
					$last_event_id = $data->content->_id;
				}

				sleep($refresh_timeout);
			} while (true);

		} else {

			try {
				// Call current request
				$this->next->call();
			} catch (Exception $e) {
				$app->content = array('error' => $e->getMessage());
			}

			$app->response->headers->set('Content-type', 'application/json');
			$app->response->setBody($this->encode_content($app->content));
		}

	}

	protected function encode_content($content) {
		if (method_exists($content, 'toJson')) {
			return $content->toJson();
		} else {
			return json_encode($content);
		}
	}

}
