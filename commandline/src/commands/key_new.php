<?php

return array(
	'arg0'    => 'key:new',
	'command' => 'key:new',
	'description' => 'Create a new key to application.',
	'run' => function($args) {

		$client = new Client\Client();
		$key = $client->post("apps/keys");

		if (!$args['json']) {
			echo "App: {$args['app']}" . PHP_EOL;
			echo "New access token:" . PHP_EOL;
			echo "{" . PHP_EOL;
			echo "\tappId: {$key->app_id}" . PHP_EOL;
			echo "\tkey: {$key->key}" . PHP_EOL;
			echo "}" . PHP_EOL . PHP_EOL;
		}

		return $key;

	}
);

