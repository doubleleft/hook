<?php

return array(
	'arg0'    => 'generate:route',
	'command' => 'generate:route <path> [<method=GET>]',
	'description' => 'Generate a custom route for the application.',
	'run' => function($args) use ($commands) {

		if (!$args[1]) {
			die("'path' is required.");
		}

		$route_path = strtolower($args[1]);
		$route_method = strtolower($args[2] ?: 'get');
		$route_method_uppercase = strtoupper($route_method);

		$route_filename = $route_method . '_' . preg_replace('/:/', '', $route_path);
		$route_filename = preg_replace('/\//', '_', $route_filename);

		// append / at the beggining of the route if it doesn't exist
		if (strpos($route_path, '/') !== 0) {
			$route_path = '/' . $route_path;
		}

		$dest = Client\Project::root() . 'dl-ext/routes/';
		$dest_file = $dest . $route_filename . '.php';
		@mkdir($dest, 0777, true);

		$arguments_list = array();
		preg_match_all('/:([a-z]+)/', $route_path, $arguments);
		foreach($arguments[1] as $arg) {
			array_push($arguments_list, '$' . $arg);
		}

		$template = file_get_contents(__DIR__ . '/../templates/route.php');
		$template = preg_replace('/{path}/', $route_path, $template);
		$template = preg_replace('/{method}/', $route_method, $template);
		$template = preg_replace('/{method_uppercase}/', $route_method_uppercase, $template);
		$template = preg_replace('/{arguments}/', implode(", ", $arguments_list), $template);

		file_put_contents($dest_file, $template);

		echo "Route created at '{$dest_file}'." . PHP_EOL;

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


