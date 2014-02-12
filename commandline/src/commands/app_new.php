<?php

return array(
	'arg0'    => 'app:new',
	'command' => 'app:new <application-name>',
	'description' => 'Create a new application.',
	'run' => function($args) {

		if (!isset($args[1])) {
			die("'application-name' is required.");
		}

		$client = new client\client();
		$app = $client->post('apps', array(
			'app' => array('name' => $args[1])
		));

		echo "App: {$app->name}" . PHP_EOL;
		echo "Access tokens:" . PHP_EOL;
		echo "{" . PHP_EOL;
		echo "\tappId: {$app->_id}" . PHP_EOL;
		foreach($app->keys as $key) {
			echo "\tkey: " . $key->key . PHP_EOL;
		}
		echo "}" . PHP_EOL . PHP_EOL;

	}
);
