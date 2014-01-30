<?php

return array(
	'arg0'    => 'modules:upload',
	'command' => 'modules:upload',
	'description' => 'Upload a module to application',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$client = new Client\Client();

		foreach(Client\Utils::glob('dl-ext/**') as $module) {
			if (is_file($module)) {
				echo "Uploading: '{$module}'" . PHP_EOL;
			}
		}

		// foreach()
		// $client->post("apps/{$args['app']}/modules");

	}
);
