<?php

class LogMiddleware extends \Slim\Middleware
{
	public function call()
	{
		$app = $this->app;

		if (!$app->request->isOptions() && $app->key) {
			// \Models\RequestLog::create(array(
			// 	'app_id' => $app->key->app_id,
			// 	'key_id' => $app->key->_id,
			// 	'uri' => $app->request->getResourceUri(),
			// 	'method' => $app->request->getMethod()
			// ));
		}

		$this->next->call();
	}
}

