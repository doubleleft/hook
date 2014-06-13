<?php

return array(
	'arg0'    => 'config',
	'command' => 'config',
	'description' => 'List all app configurations',
	'run' => function($args) {
		$client = new Client\Client();
		$configs = $client->get("apps/configs");

		$project = Client\Project::getConfig();

		if (!$args['json']) {
			foreach($project as $key => $value) {
				echo $key . ': ' . $value . PHP_EOL;
			}
			echo str_repeat('-', 37) . PHP_EOL;
			if ($configs) {
				foreach($configs as $config) {
					preg_match('/([^$|\n]+)/', $config->value, $value);
					echo $config->name . ': ' . $value[1] . PHP_EOL;
				}
			} else {
				echo "No configurations found for: '{$project['name']}'." . PHP_EOL;
			}
		}

		return $configs;
	}
);


