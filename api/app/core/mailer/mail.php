<?php

class Mail {

	public static function getTransport($params = array()) {
		// Validate SMTP params
		if ($params['driver'] == 'smtp' && (!isset($params['username']) || !isset($params['password']))) {
			throw new Exception("'mail.username' and 'mail.password' configs are required when using 'smtp' driver;");
		}

		$transport_klass = '\Swift_'.ucfirst(strtolower($params['driver'])).'Transport';
		$transport = $transport_klass::newInstance();
		unset($params['driver']);

		// Set custom transport params
		foreach($params as $param => $value) {
			$transport->{'set'.ucfirst($param)}($value);
		}

		return $transport;
	}

	public static function sendMessage($transport, $options) {
		// Validate options
		if (!isset($options['to'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'to' option is required.");
		}

		if (!isset($options['template'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'template' option is required.");
		}

		// Compile template body
		$body = models\Module::template($options['template'])
			->compile($options['data']);

		$mailer = \Swift_Mailer::newInstance($transport);
		$message = \Swift_Message::newInstance($options['subject'])
			->setFrom($options['from'])
			->setTo($options['to'])
			->setBody($body);

		return $mailer->send($message);
	}

	public static function send($options = array()) {
		models\AppConfig::getAll('mail.%')->each(function($config) use (&$params) {
			preg_match('/mail\.([a-z]+)/', $config->name, $matches);
			$params[ $matches[1] ] = $config->value;
		});

		// set 'mail' as default driver
		if (!isset($params['driver'])) {
			$params['driver'] = 'mail';
		} else {

			$preset_file = __DIR__ . '/presets/' . $params['driver'] . '.php';
			if (file_exists($preset_file)) {
				$params = array_merge($params, require($preset_file));
			}

		}

		$transport = static::getTransport($params);
		return static::sendMessage($transport, $options);
	}

}
