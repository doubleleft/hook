<?php

return array(
	'arg0'    => 'config:set',
	'command' => 'config:set <name=value> [<name=value> ...]',
	'description' => 'Set a configuration to app.',
	'run' => function($args) use ($commands) {

		$configs_to_add = array();
		foreach($args as $arg) {
			if (!is_null($arg) && preg_match('/=/', $arg)) {
				$config = preg_split('/=/', $arg);
				$name = $config[0];
				$value = $config[1];

				//
				// Read or extract certificate file
				// --------------------------------
				//
				if (file_exists($value)) {
					\Client\Console::output("Reading certificate file..." . PHP_EOL);

					$ext = pathinfo($value, PATHINFO_EXTENSION);
					if ($ext == 'p12') {
						$results = array();
						$worked = openssl_pkcs12_read(file_get_contents($value), $results, null);
						if ($worked) {
							$value = $results['cert'] . $results['pkey'];
						} else {
							\Client\Console::error(openssl_error_string());
						}
					} else if ($ext == 'pem') {
						$value = file_get_contents($value);
					}
				}

				array_push($configs_to_add, array(
					'name' => $name,
					'value' => $value
				));
			}
		}

		$client = new Client\Client();
		$configs = $client->post("apps/configs", array(
			'configs' => $configs_to_add
		));

		// Run 'config' command after config:add
		$commands['config']['run']($args);
	}
);



