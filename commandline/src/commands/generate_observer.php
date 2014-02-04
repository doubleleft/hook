<?php

return array(
	'arg0'    => 'generate:observer',
	'command' => 'generate:observer <collection-name>',
	'description' => 'Generate observer class for collection events.',
	'run' => function($args) use ($commands) {
		if (!isset($args[1])) {
			die("'collection-name' is required.");
		}
		$dest = 'dl-ext/observers/';
		$dest_file = $dest . $args[1] . '.php';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../templates/observer.php');
		$template = preg_replace('/{name}/', ucfirst($args[1]), $template);
		file_put_contents($dest_file, $template);

		echo "Observer created at '{$dest_file}'." . PHP_EOL;
	}
);
