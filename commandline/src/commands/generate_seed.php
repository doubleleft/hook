<?php

return array(
	'arg0'    => 'generate:seed',
	'command' => 'generate:seed <collection-name>',
	'description' => 'Generate seed template.',
	'run' => function($args) use ($commands) {

		if (!isset($args[1])) {
			die("'collection-name' is required.");
		}

		$collection = strtolower($args[1]);

		$dest = Client\Project::root() . 'dl-ext/seeds/';
		$dest_file = $dest . $collection . '.yaml';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../templates/seed.yaml');
		$template = preg_replace('/{name}/', $collection, $template);
		file_put_contents($dest_file, $template);

		echo "Seed created at '{$dest_file}'." . PHP_EOL;

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


