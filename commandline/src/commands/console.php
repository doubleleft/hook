<?php

return array(
	'arg0'    => 'console',
	'command' => 'console',
	'description' => 'Start interactive console.',
	'run' => function($args) use ($commands) {
		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$process = proc_open(
			'node ' . __DIR__ . '/../../console/bootstrap.js ' . Client\Project::root() . Client\Project::CONFIG_FILE,
			$descriptors,
			$pipes
		);
	}
);
