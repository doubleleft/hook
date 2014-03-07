<?php

return array(
	'arg0'    => 'module:upload',
	'command' => 'module:upload',
	'description' => 'Upload a module to application',
	'run' => function($args) {
		$module_types = array('observers', 'routes', 'templates');

		$client = new Client\Client();
		foreach(Client\Utils::glob(Client\Project::root() . 'dl-ext/**') as $module) {

			if (is_file($module)) {
				$module_type = basename(dirname($module));

				if (!in_array($module_type, $module_types)) {
					// echo "Invalid module type: '{$module_type}'." . PHP_EOL;
					continue;
				}

				// Check for syntax problems before uploading it.
				$lint_output = null;
				$lint_return_code = null;
				exec('php --syntax-check ' . $module, $lint_output, $lint_return_code);
				if ($lint_return_code !== 0) {
					echo "Aborting." . PHP_EOL;
					die();
				}

				echo "Uploading: '{$module}'" . PHP_EOL;

				$uploaded = $client->post('apps/modules', array(
					'module' => array(
						'name' => basename($module),
						'type' => $module_type,
						'code' => file_get_contents($module)
					)
				));

			}
		}

	}
);
