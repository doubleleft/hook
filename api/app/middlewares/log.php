<?php

class LogMiddleware extends \Slim\Middleware
{
	public function call()
	{
		$app = $this->app;

		//
		// TODO: find a way to enable/disable logs for production use
		//
		if (!$app->request->isOptions() && $app->key) {
			// set application log writer for this app
			$this->app->log->setWriter(new \LogWriter(storage_dir() . '/logs.txt'));

			if ($app->request->getResourceUri() !== '/apps/logs') {
				$log = $app->request->getIp() . ' - [' . date('d-m-Y H:i:s') . '] ';
				$log .= $app->request->getMethod() . ' ' . $app->request->getResourceUri() . PHP_EOL;
				$log .= 'Params: ' . json_encode($app->request->params()) . PHP_EOL;

				$app->log->info($log);
			}
		}

		$this->next->call();
	}
}
