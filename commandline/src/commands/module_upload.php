<?php

return array(
	'arg0'    => 'module:upload',
	'command' => 'module:upload',
	'description' => 'Upload a module to application',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$app = $args['app'];
		$module_types = array('observers', 'routes', 'templates');

		$client = new client\client();
		foreach(client\utils::glob('dl-ext/**') as $module) {

			if (is_file($module)) {
				$module_type = basename(dirname($module));

				if (!in_array($module_type, $module_types)) {
					echo "Invalid module type: '{$module_type}'." . PHP_EOL;
					continue;
				}

				echo "Uploading: '{$module}'" . PHP_EOL;

				$client->post('apps/'.$app.'/modules', array(
					'module' => array(
						'name' => basename($module),
						'type' => $module_type,
						'code' => file_get_contents($module)
					)
				));

			}
		}

		// foreach()
		// $client->post("apps/{$args['app']}/modules");

	}
);
