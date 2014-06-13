<?php

return array(
	'arg0'    => 'db:seed',
	'command' => 'db:seed [<seed-file>]',
	'description' => 'Seed collections from YAML files.',
	'run' => function($args) use ($commands) {
		$seed_file = '*.yaml';

		if ($args[1] !== null) {
			$seed_file = $args[1] . '.yaml';
		}

		$client = new Client\Client();
		foreach(\Client\Utils::glob(Client\Project::root() . 'dl-ext/seeds/' . $seed_file) as $yaml_file) {
			$collection = basename($yaml_file, '.yaml');

			$yaml = new Symfony\Component\Yaml\Parser();
			$options = $yaml->parse(file_get_contents($yaml_file));

			if (isset($options['truncate']) && $options['truncate']) {
				echo "Truncating '{$collection}'... ";
				$truncate = $client->delete('collection/' . $collection);
				if (count($truncate)>0) {
					echo "ok.";
				}
				echo PHP_EOL;
			}

			if (isset($options['data']) && $options['data']) {
				$current_row = 0;
				$total_rows = count($options['data']);
				foreach($options['data'] as $data) {

					// Look for special data fields
					foreach($data as $field => $value) {
						if (preg_match('/\!upload ([^$]+)/', $value, $file)) {
							$filepath = 'dl-ext/seeds/' . $file[1];

							// stop when file doens't exists
							if (!file_exists($filepath)) {
								Client\Console::error("File not found: '{$filepath}'");
								die();
							}

							$mime_type = Client\Utils::mime_type($filepath);
							$data[$field] = 'data:' . $mime_type . ';base64,' . base64_encode(file_get_contents($filepath));
						}
					}

					$client->post('collection/' . $collection, array('data' => $data));
					$current_row += 1;
					$percent = round(($current_row / $total_rows)*100);
					echo "Seeding '{$collection}': " . "{$percent}%" . str_repeat("\r", strlen($percent)+1);
				}
			}

			echo PHP_EOL;
		}
		echo "Done." . PHP_EOL;
	}
);

