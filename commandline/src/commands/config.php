<?php

return array(
	'arg0'    => 'config',
	'command' => 'config',
	'description' => 'List all app configurations',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$client = new Client\Client();
		$configs = $client->get("apps/{$args['app']}/configs");

		if ($configs) {
			foreach($configs as $config) {
				echo $config->name . ': ' . $config->value . PHP_EOL;
			}
		} else {
			echo "No configurations found for this app." . PHP_EOL;
		}


	}
);


