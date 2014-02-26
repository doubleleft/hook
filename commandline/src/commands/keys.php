<?php

return array(
	'arg0'    => 'keys',
	'command' => 'keys',
	'description' => 'List all application keys.',
	'run' => function($args) {

		$client = new Client\Client();
		$app = $client->get("apps/keys");

		if (!$args['json']) {
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
		}

		return $app;

	}
);


