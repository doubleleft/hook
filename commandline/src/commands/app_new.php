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
		$client->post('apps', array(
			'app' => array(
				'name' => $args[1]
			)
		));

	}
);
