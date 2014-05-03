<?php

return array(
	'arg0'    => 'logs',
	'command' => 'logs',
	'description' => 'Get app back-end logs.',
	'run' => function($cli) {
		$url = "apps/logs";

		if ($cli['tail']) {
			$url .= '?tail=1';
		}

		$client = new Client\Client();
		$logs = $client->get($url);
		echo $cli['tail'] . PHP_EOL;

		print ($cli['tail'] ? 'Sim' : 'Nao') . PHP_EOL;
		// return $logs;
	}
);



