<?php

return array(
	'arg0'    => 'schedule',
	'command' => 'schedule',
	'description' => 'List tasks scheduled for app.',
	'run' => function($args) {

		$client = new Client\Client();
		$schedule = $client->get("apps/tasks");

		$project = Client\Project::getConfig();

		if (!$args['json']) {
			if ($schedule) {
				Client\Console::output("# crontab");
				foreach($schedule as $task) {
					Client\Console::output($task->command);
				}
			} else {
				Client\Console::output("No tasks scheduled for: '{$project['name']}'.");
			}
		}

		return $schedule;
	}
);
