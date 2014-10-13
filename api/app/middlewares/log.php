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
			$log_file = storage_dir() . '/logs.txt';

			// set application log writer for this app
			$this->app->log->setWriter(new \LogWriter($log_file));

			// disable log if storage directory doesn't exists.
			// maybe we're on a readonly filesystem
			$this->app->log->setEnabled(file_exists($log_file));

			if (strpos($app->request->getPath(), "/apps/") === false) {
				$app->log->info($app->request->getIp() . ' - [' . date('d-m-Y H:i:s') . '] ' . $app->request->getMethod() . ' ' . $app->request->getResourceUri());
				$app->log->info('Params: ' . json_encode($app->request->params()));
			}
		}

		$this->next->call();
	}
}
