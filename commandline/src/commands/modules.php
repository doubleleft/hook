<?php

return array(
	'arg0'    => 'modules',
	'command' => 'modules',
	'description' => 'List all application modules',
	'run' => function($args) {

		$client = new Client\Client();
		$modules = $client->get("apps/modules");


		if (!$args['json']) {
			if ($modules) {
				echo "Modules: " . PHP_EOL;
				foreach ($modules as $module) {
					echo "\t'{$module->name}' ({$module->type}) - LoC: " . substr_count($module->code, "\n") . PHP_EOL;
				}
			} else {
				$project = Client\Project::getConfig();
				echo "No modules found for: '{$project['name']}'." . PHP_EOL;
			}
		}

		return $modules;
	}
);
