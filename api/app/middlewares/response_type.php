<?php

class ResponseTypeMiddleware extends \Slim\Middleware
{

	public function call()
	{
		// The Slim application
		$app = $this->app;

		// Respond based on ACCEPT request header
		if ($app->request->headers->get('ACCEPT') == 'text/event-stream') {
			$pool_start = time();

			do {
				// Close EventSource connection after 10 seconds
				// let the client re-open it if necessary
				if ((time() - $pool_start) > 4) {
					die();
				}

				$this->next->call();

				$app->response->headers->set('Content-type', 'text/event-stream');
				if (method_exists($app->content, 'each')) {
					$app->content->each(function($data) use ($app) {
						$app->response->write('data: '. $data->toJson() . "\n");
						$app->response->write(PHP_EOL);
						ob_flush();
						flush();
					});

				} else {
					$app->response->write('data: '. $app->content->toJson() . "\n");
					$app->response->write(PHP_EOL);
					ob_flush();
					flush();
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

}


