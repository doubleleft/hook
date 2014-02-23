<?php

return array(
	'arg0'    => 'apps',
	'command' => 'apps',
	'description' => 'List all applications',
	'run' => function($args) {

		$client = new Client\Client();
		$apps = $client->get('apps/list');

		if (!$args['json']) {
			if ($apps) {
				foreach($apps as $app) {
					echo "App: {$app->name}" . PHP_EOL;
					if (count($app->keys) > 0) {
						echo "Access tokens:" . PHP_EOL;
						foreach($app->keys as $key) {
							echo "{" . PHP_EOL;
							echo "\tappId: {$app->_id}" . PHP_EOL;
							echo "\tkey: " . $key->key . PHP_EOL;
							if ($key->admin) {
								echo "\tadmin: {$key->admin}" . PHP_EOL;
							}
							echo "}" . PHP_EOL;
						}
					}
					echo PHP_EOL;
				}
			} else {
				echo "No apps found." . PHP_EOL;
			}
		}

		return $apps;
	}
);

