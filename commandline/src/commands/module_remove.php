<?php

return array(
	'arg0'    => 'module:remove',
	'command' => 'module:remove <module-name>',
	'description' => 'Remove a module from application',
	'run' => function($args) {
		$module = (isset($args['1'])) ? $args['1'] : false;

		if (!$module) {
			die("Error: 'module-name' is required." . PHP_EOL);
		}

		$client = new Client\Client();
		$response = $client->delete('apps/modules', array(
			'module' => array('name' => $module)
		));

		if ($response->success) {
			echo "Module '{$module}' removed successfully." . PHP_EOL;
		} else {
			echo "Module '{$module}' not found." . PHP_EOL;
		}

	}
);
