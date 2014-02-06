<?php

return array(
	'arg0'    => 'generate:email',
	'command' => 'generate:email <template-name>',
	'description' => 'Generate email template.',
	'run' => function($args) use ($commands) {

		if (!isset($args[1])) {
			die("'template-name' is required.");
		}

		$template_name = basename($args[1], '.html');

		$dest = 'dl-ext/templates/';
		$dest_file = $dest . $template_name . '.html';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../templates/email.html');
		$template = preg_replace('/{template_name}/', ucfirst($template_name), $template);
		file_put_contents($dest_file, $template);

		echo "Template created at '{$dest_file}'." . PHP_EOL;
	}
);

