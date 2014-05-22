<?php

return array(
	'arg0'    => 'config:remove',
	'command' => 'config:remove <name> [<name> ...]',
	'description' => 'Remove configuration from app.',
	'run' => function($args) use ($commands) {
		$config = isset($args[1]) ? $args[1] : null;

		if (is_null($config)) {
			die("Error: you must provide a config name to remove.");
		}

		$client = new Client\Client();
		$configs = $client->delete("apps/configs/" . $config);

		if ($configs && $configs->success) {
			echo "{$config} removed successfully." . PHP_EOL;
		}

		echo '...' . PHP_EOL;

		// Run 'config' command after config:add
		$commands['config']['run']($args);
	}
);

