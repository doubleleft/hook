<?php

class EventSourceMiddleware extends \Slim\Middleware
{

	public function call()
	{
		// The Slim application
		$app = $this->app;
		$pool_start = time();

		$app->response->headers->set('Content-type', 'text/event-stream');

		do {
			// Close EventSource connection after 10 seconds
			// let the client re-open it if necessary
			if ((time() - $pool_start) > 10) {
				die();
			}

			ob_start();
			$this->next->call();
			$json = ob_get_contents();
			// var_dump($json);
			ob_end_flush();

			$data = 'data: ' . $json;

			$app->response->setBody($json);
			sleep(2);
		} while (true);
	}

}

