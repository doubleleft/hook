<?php

return array(
	'arg0'    => 'logs',
	'command' => 'logs',
	'description' => 'Get app back-end logs.',
	'run' => function($cli) {
		$url = "apps/logs";
		$data = array();

		// TODO: http stream not working properly
		//
		// if ($cli['tail']) {
		// 	$url .= '?' . urlencode(json_encode(array('tail' => 1)));
		// }

		$client = new Client\Client();
		$request = $client->request('get', $url);

		if ($cli['tail']) {
			// read from stream
			$factory = new \Guzzle\Stream\PhpStreamRequestFactory();
			$stream = $factory->fromRequest($request);

			while (!$stream->feof()) {
				echo $stream->readLine();
			}

		} else {
			// just output response
			echo $request->send()->getBody(true);
		}

	}
);



