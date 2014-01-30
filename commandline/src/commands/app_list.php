<?php

return array(
	'command' => 'app:list',
	'description' => 'List all applications',
	'run' => function($args) {

		$client = new Client\Client();
		foreach($client->get('apps') as $app) {
			echo "Id: {$app->_id}" . PHP_EOL;
			echo "Name: {$app->name}" . PHP_EOL;
			foreach($app->keys as $key) {
				echo "\tKey: " . $key->key . PHP_EOL;
			}
		}

	}
);

