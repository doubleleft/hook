<?php

return array(
	'arg0'    => 'db:seed',
	'command' => 'db:seed [<seed-file>]',
	'description' => 'Generate observer class for collection events.',
	'run' => function($args) use ($commands) {
		$seed_file = '*';

		if ($args[1]!==null) {
			$seed_file = $args[1];
		}

		$client = new Client\Client();
		foreach(\Client\Utils::glob('dl-ext/seeds/' . $seed_file) as $yaml_file) {
			$collection = basename($yaml_file, '.yaml');
			echo "Seeding '{$collection}'..." . PHP_EOL;

			$yaml = new Symfony\Component\Yaml\Parser();
			$options = $yaml->parse(file_get_contents($yaml_file));
			if (!isset($options['truncate']) && $options['truncate']) {
				echo "Truncating... ";

				$drop = $client->delete('collection/' . $collection);
				var_dump($drop);
			}
			var_dump($options);
		}
	}
);

