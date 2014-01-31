<?php

return array(
	'arg0'    => 'modules:list',
	'command' => 'modules:list',
	'description' => 'List all application modules',
	'run' => function($args) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$client = new Client\Client();
		$modules = $client->get("apps/{$args['app']}/modules");

		if ($modules) {
			echo "Modules: " . PHP_EOL;
			foreach ($modules as $module) {
				echo "\t'{$module->name}' ({$module->type}) - LoC: " . substr_count($module->code, "\n") . PHP_EOL;
			}
		} else {
			echo "No modules found for: '{$args['app']}'." . PHP_EOL;
		}

	}
);