<?php

class LogMiddleware extends \Slim\Middleware
{
	public function call()
	{
		if (!$app->request->isOptions()) {
			$app = $this->app;

			\Models\RequestLog::create(array(
				'app_id' => $app->key->app_id,
				'uri' => $app->request->getResourceUri(),
				'method' => $app->request->getMethod()
			));

			$this->next->call();
		}
	}
}

