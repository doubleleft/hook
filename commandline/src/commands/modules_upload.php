<?php

return array(
	'command' => 'modules:upload',
	'description' => 'Upload a module to application',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$client = new Client\Client();

		foreach(glob('dl-ext/**') as $module) {
			var_dump($module);
		}

		// foreach()
		// $client->post("apps/{$args['app']}/modules");

	}
);
