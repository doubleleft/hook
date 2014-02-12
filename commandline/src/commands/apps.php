<?php

return array(
	'arg0'    => 'apps',
	'command' => 'apps',
	'description' => 'List all applications',
	'run' => function($args) {

		$client = new client\client();
		foreach($client->get('apps') as $app) {
			echo "App: {$app->name}" . PHP_EOL;
			echo "Access tokens:" . PHP_EOL;
			echo "{" . PHP_EOL;
			echo "\tappId: {$app->_id}" . PHP_EOL;
			foreach($app->keys as $key) {
				echo "\tkey: " . $key->key . PHP_EOL;
			}
			echo "}" . PHP_EOL . PHP_EOL;
		}

	}
);

