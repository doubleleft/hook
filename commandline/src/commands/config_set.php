<?php

return array(
	'arg0'    => 'config:set',
	'command' => 'config:set',
	'description' => 'Set a configuration to app.',
	'run' => function($args) use ($commands) {

		if (!$args['app']) {
			die("Error: '--app' option is required" . PHP_EOL);
		}

		$configs_to_add = array();
		foreach($args as $arg) {
			if (!is_null($arg) && preg_match('/=/', $arg)) {
				$config = preg_split('/=/', $arg);
				array_push($configs_to_add, array(
					'name' => $config[0],
					'value' => $config[1]
				));
			}
		}

		$client = new Client\Client();
		$configs = $client->post("apps/{$args['app']}/configs", array(
			'configs' => $configs_to_add
		));

		// Run 'config' command after config:add
		$commands['config']['run']($args);
	}
);



