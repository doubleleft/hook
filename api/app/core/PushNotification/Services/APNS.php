<?php
namespace PushNotification\Services;

class APNS {

	/**
	 * push
	 * @param mixed $registrations
	 * @param mixed $data
	 */
	public function push($registrations, $data) {
		// Instantiate a new ApnsPHP_Push object
		$push = new \ApnsPHP_Push(
			\ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
			// 'server_certificates_bundle_sandbox.pem'
			__DIR__ . '/cert.pem'
		);

		// Set the Provider Certificate passphrase
		// $push->setProviderCertificatePassphrase('test');

		// Set the Root Certificate Autority to verify the Apple remote peer
		// $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');

		// Connect to the Apple Push Notification Service
		$push->connect();

		$message = new \ApnsPHP_Message();

		// Add all registrations as message recipient
		foreach($registrations as $registration) {
			$message->addRecipient($registration->device_id);
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
		if ($data['text']) {
			$message->setText($data['text']);
		}

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

		if (!empty($error_list)) {
			var_dump($error_list);
			return false;
		}

		return true;
	}

}
