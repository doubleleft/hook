<?php

return array(
	'arg0'    => 'generate:observer',
	'command' => 'generate:observer <collection-name>',
	'description' => 'Generate observer class for collection events.',
	'run' => function($args) use ($commands) {

		if (!isset($args[1])) {
			die("'collection-name' is required.");
		}

		$dest = Client\Project::root() . 'dl-ext/observers/';
		$dest_file = $dest . $args[1] . '.php';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../templates/observer.php');
		$template = preg_replace('/{name}/', ucfirst($args[1]), $template);
		$template = preg_replace('/{collection}/', $args[1], $template);
		file_put_contents($dest_file, $template);

		echo "Observer created at '{$dest_file}'." . PHP_EOL;

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
