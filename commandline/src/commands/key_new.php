<?php

return array(
	'arg0'    => 'key:new',
	'command' => 'key:new',
	'description' => 'Create a new key to application.',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$client = new client\client();
		$key = $client->post("apps/{$args['app']}/keys");

		echo "App: {$args['app']}" . PHP_EOL;
		echo "New access token:" . PHP_EOL;
		echo "{" . PHP_EOL;
		echo "\tappId: {$key->app_id}" . PHP_EOL;
		echo "\tkey: {$key->key}" . PHP_EOL;
		echo "}" . PHP_EOL . PHP_EOL;

	}
);

