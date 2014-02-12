<?php

return array(
	'arg0'    => 'module:remove',
	'command' => 'module:remove <module-name>',
	'description' => 'Remove a module from application',
	'run' => function($args) {
		$app = (isset($args['app'])) ? $args['app'] : false;
		$module = (isset($args['1'])) ? $args['1'] : false;

		if (!$app) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		if (!$module) {
			die("Error: 'module-name' is required." . PHP_EOL);
		}

		$client = new client\client();
		$response = $client->delete('apps/'.$app.'/modules/'.$module);

		if ($response->success) {
			echo "Module '{$module}' removed successfully." . PHP_EOL;
		} else {
			echo "Module '{$module}' not found." . PHP_EOL;
		}

	}
);
