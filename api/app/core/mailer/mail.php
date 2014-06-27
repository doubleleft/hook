<?php

class Mail {

	public static function getTransport($params = array()) {
		// Validate SMTP params
		if ($params['driver'] == 'smtp' && (!isset($params['username']) || !isset($params['password']))) {
			throw new Exception("'mail.username' and 'mail.password' configs are required when using 'smtp' driver;");
		}

		$transport_klass = '\Swift_'.ucfirst(strtolower($params['driver'])).'Transport';
		$transport = call_user_func(array($transport_klass, 'newInstance'));
		unset($params['driver']);

		// Set custom transport params
		foreach($params as $param => $value) {
			call_user_func(array($transport, 'set' . ucfirst($param)), $value);
		}

		return $transport;
	}

	public static function sendMessage($transport, $options) {
		// Validate options
		if (!isset($options['to'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'to' option is required.");
		}

		if (!isset($options['body'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'body' option is required.");
		}

		if (!isset($options['from'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'from' option is required.");
		}

		// Use text/html as default content-type
		if (!isset($options['contentType'])) {
			$options['contentType'] = 'text/html';
		}

		$mailer = \Swift_Mailer::newInstance($transport);
		$message = \Swift_Message::newInstance($options['subject'])
			->setFrom($options['from'])
			->setTo($options['to'])
			->setContentType($options['contentType'])
			->setBody($options['body']);

		return $mailer->send($message);
	}

	public static function send($options = array()) {
		$params = array();

		models\AppConfig::current()
			->where('app_id', \Model\App::currentId())
			->where(function($query) {
				$query->where('name', 'mail.driver')
					->orWhere('name', 'mail.host')
					->orWhere('name', 'mail.port')
					->orWhere('name', 'mail.encryption')
					->orWhere('name', 'mail.username')
					->orWhere('name', 'mail.password');
			})
			->get()
			->each(function($config) use (&$params) {
			preg_match('/mail\.([a-z]+)/', $config->name, $matches);
			$params[ $matches[1] ] = $config->value;
		});

		// set 'mail' as default driver
		if (!isset($params['driver'])) {
			$params['driver'] = 'mail';
		} else {

			$preset_file = __DIR__ . '/presets/' . $params['driver'] . '.php';
			if (file_exists($preset_file)) {
				$preset_params = require($preset_file);
				unset($params['driver']);

				// allow to overwrite default preset settings with custom configs
				$params = array_merge($preset_params, $params);
			}

		}

		$transport = static::getTransport($params);
		return static::sendMessage($transport, $options);
	}

}
