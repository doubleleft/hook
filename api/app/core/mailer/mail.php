<?php

class Mail {

	public static function send($options = array()) {
		if (!isset($options['to'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'to' option is required.");
		}

		if (!isset($options['template'])) {
			throw new \Exception(__CLASS__ . "::".__METHOD__.": 'template' option is required.");
		}

		$app = \Slim\Slim::getInstance();
		// $app->
	}

}
