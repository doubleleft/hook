<?php

return array(
	'arg0'    => 'app:new',
	'command' => 'app:new <application-name>',
	'description' => 'Create a new application.',
	'run' => function($args) {

		if (!isset($args[1])) {
			die("'application-name' is required.");
		}

		$client = new Client\Client();
		$app = $client->post('apps', array(
			'app' => array('name' => $args[1])
		));

		Client\Project::setConfig(array(
			'name' => $app->name,
			'app_id' => $app->keys[0]->app_id,
			'key' => $app->keys[0]->key
		));

		if (!$args['json']) {
			echo "App: {$app->name}" . PHP_EOL;
			echo "Access tokens:" . PHP_EOL;
			foreach($app->keys as $key) {
				echo "{" . PHP_EOL;
				if ($key->admin) {
					echo "\tadmin: " . $key->admin . PHP_EOL;
				}
				echo "\tappId: {$app->_id}" . PHP_EOL;
				echo "\tkey: " . $key->key . PHP_EOL;
				echo "}" . PHP_EOL;
			}
		}

		return $app->keys;

	}
);
