<?php

return array(
	'arg0'    => 'config',
	'command' => 'config',
	'description' => 'List all app configurations',
	'run' => function($args) {
		$project = Client\Project::getConfig();

		$client = new Client\Client();
		$configs = $client->get("apps/configs");

		if (!$args['json']) {
			if ($configs) {
				foreach($configs as $config) {
					echo $config->name . ': ' . $config->value . PHP_EOL;
				}
			} else {
				echo "No configurations found for: '{$project['name']}'." . PHP_EOL;
			}
		}

		return $configs;
	}
);


