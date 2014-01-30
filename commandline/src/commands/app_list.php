<?php

return array(
	'arg0'    => 'app:list',
	'command' => 'app:list',
	'description' => 'List all applications',
	'run' => function($args) {

		$client = new Client\Client();
		foreach($client->get('apps') as $app) {
			echo "App: {$app->name}" . PHP_EOL;
			echo "Access tokens:" . PHP_EOL;
			echo "{" . PHP_EOL;
			echo "\tappId: {$app->_id}" . PHP_EOL;
			foreach($app->keys as $key) {
				echo "\tkey: " . $key->key . PHP_EOL;
			}
			echo "}" . PHP_EOL;
		}

	}
);

