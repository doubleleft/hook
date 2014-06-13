<?php

return array(
	'arg0'    => 'generate:channel',
	'command' => 'generate:channel <channel-name>',
	'description' => 'Generate custom channel class.',
	'run' => function($args) use ($commands) {

		if (!isset($args[1])) {
			die("'channel-name' is required.");
		}

		$dest = Client\Project::root() . 'dl-ext/channels/';
		$dest_file = $dest . $args[1] . '.php';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../templates/channel.php');
		$template = preg_replace('/{name}/', ucfirst($args[1]), $template);
		$template = preg_replace('/{channel}/', $args[1], $template);
		file_put_contents($dest_file, $template);

		echo "Custom channel created at '{$dest_file}'." . PHP_EOL;

		if ($editor = getenv('EDITOR')) {
			$descriptors = array(
				array('file', '/dev/tty', 'r'),
				array('file', '/dev/tty', 'w'),
				array('file', '/dev/tty', 'w')
			);
			$process = proc_open("{$editor} {$dest_file}", $descriptors, $pipes);
		}
	}
);

