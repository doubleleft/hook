<?php

return array(
	'arg0'    => 'config:remove',
	'command' => 'config:remove',
	'description' => 'Remove configuration from app.',
	'run' => function($args) use ($commands) {

		$config = isset($args[1]) ? $args[1] : null;

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		if (is_null($config)) {
			die("Error: you must provide a config name to remove.");
		}

		$client = new client\client();
		$configs = $client->delete("apps/{$args['app']}/configs/" . $config);

		if ($configs && $configs->success) {
			echo "{$config} removed successfully." . PHP_EOL;
		}

		// Run 'config' command after config:add
		$commands['config']['run']($args);
	}
);




