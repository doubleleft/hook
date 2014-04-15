<?php

return array(
	'arg0'    => 'console',
	'command' => 'console [evaluate_file.js]',
	'description' => 'Start interactive console, or run filename.',
	'run' => function($args) use ($commands) {

		$dl_config_path = Client\Project::getConfigFile();
		if (!file_exists($dl_config_path)) {
			die("No .dl-config file found at project root.\n");
		}

		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$process = proc_open(
			'node ' . __DIR__ . '/../../console/bootstrap.js ' . $dl_config_path . ' ' . $args[1],
			$descriptors,
			$pipes
		);
	}
);
