<?php
namespace PushNotification\Services;

class APNS implements Service {

	/**
	 * push
	 * @param mixed $registrations
	 * @param mixed $data
	 */
	public function push($registrations, $data) {
		$apns_environment = \models\AppConfig::get('push.apns.environment', 'sandbox');
		$apns_certificate_file = \models\AppConfig::get('push.apns.cert.file', false);
		$apns_certificate_pass = \models\AppConfig::get('push.apns.cert.pass', false);

		if (!$apns_certificate_file) {
			throw new \Exception("APNS config error: 'push.apns.cert.file' not set.");
		}

		$app = \Slim\Slim::getInstance();
		$total_errors = 0;

		// Instantiate a new ApnsPHP_Push object
		$push = new \ApnsPHP_Push(
			($apns_environment == 'sandbox') ? \ApnsPHP_Abstract::ENVIRONMENT_SANDBOX : \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
			$this->getCertificateFile($apns_certificate_file)
		);

		// Set the Provider Certificate passphrase
		if ($apns_certificate_pass) {
			$push->setProviderCertificatePassphrase($apns_certificate_pass);
		}

		// Set the Root Certificate Autority to verify the Apple remote peer
		// $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');

		// Connect to the Apple Push Notification Service
		$push->connect();

		$message = new \ApnsPHP_Message();

		// Add all registrations as message recipient
		foreach($registrations as $registration) {
			try {
				$message->addRecipient($registration->device_id);
			} catch (\ApnsPHP_Message_Exception $e) {
				$app->log->info($e->getMessage());
				$total_errors +=1;
			}
		}

		// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
		// over a ApnsPHP_Message object retrieved with the getErrors() message.
		if (isset($data['custom_identifier'])) {
			$message->setCustomIdentifier($data['custom_identifier']);
		}

		// Set badge icon to "3"
		if (is_int($data['badge'])) {
			$message->setBadge((int)$data['badge']);
		}

		// Set text
		$message->setText($data['message']);

		// Play the default sound
		if (!isset($data['sound']) || empty($data['sound'])) {
			$data['sound'] = 'default';
		}
		$message->setSound($data['sound']);

		// Set the expiry value to 30 seconds
		if (isset($data['expiry']) && $data['expiry'] > 0) {
			$message->setExpiry($data['expiry']);
		}

		// Set custom properties
		$invalid_properties = array(
			'_id', 'app_id',
			'created_at', 'updated_at',
			'sound', 'text', 'badge',
			'expiry', 'custom_identifier'
		);
		$custom_properties = array_diff_key($data, array_flip($invalid_properties));
		foreach($custom_properties as $property => $value) {
			$message->setCustomProperty($property, $value);
		}

		// Add the message to the message queue
		$push->add($message);

		// Send all messages in the message queue
		$push->send();

		// Disconnect from the Apple Push Notification Service
		$push->disconnect();

		// Examine the error message container
		$error_list = $push->getErrors();

		// Log delivery status
		$errors = $push->getErrors();
		$total_errors += count($errors);

		if ($total_errors > 0) {
			foreach($errors as $error) {
				$app->log->info($errors);
			}
		}

		return array(
			'success' => $registrations->count() - $total_errors,
			'errors' => $total_errors
		);
	}

	/**
	 * getCertificateFile
	 * @param string $contents
	 */
	protected function getCertificateFile($contents) {
		$filename = storage_dir() . '/' . md5($contents) . '.pem';

		if (!file_exists($filename)) {
			file_put_contents($filename, $contents);
		}

		return $filename;
	}

}
