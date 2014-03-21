<?php

return array(
	'arg0'    => 'schedule:upload',
	'command' => 'schedule:upload',
	'description' => 'Upload application schedule.',
	'run' => function($args) {
		$module_types = array('observers', 'routes', 'templates');

		$client = new Client\Client();
		$schedule_file = Client\Project::root() . 'dl-ext/schedule.yaml';

		$uploaded = null;
		if (file_exists($schedule_file)) {
			$yaml = new Symfony\Component\Yaml\Parser();
			$schedule_data = $yaml->parse(file_get_contents($schedule_file));

			echo "Uploading: '{$schedule_file}'" . PHP_EOL;
			$uploaded = $client->post('apps/tasks', $schedule_data);
			if ($uploaded->success) {
				Client\Console::output('Crontab installed successfully.');
			} else {
				Client\Console::error("Error to install crontab.");
			}
		} else {
			Client\Console::error("File not found: " . $schedule_file);
			Client\Console::output('To generate it run: ' . PHP_EOL . "\tdl-api generate:schedule");
		}

		return $uploaded;
	}
);

