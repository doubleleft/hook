<?php

class LogMiddleware extends \Slim\Middleware
{
	public function call()
	{
		$app = $this->app;

		if (!$app->request->isOptions() && $app->key) {
			$log = $app->request->getIp() . ' - [' . date('d-m-Y H:i:s') . '] ';
			$log .= $app->request->getMethod() . ' ' . $app->request->getResourceUri() . PHP_EOL;
			$log .= 'Parameters: ' . json_encode($app->request->params()) . PHP_EOL . PHP_EOL;

			$fp = fopen(storage_dir() . '/logs.txt', 'a+');
			fwrite($fp, $log);
			fclose($fp);
		}

		$this->next->call();
	}
}

