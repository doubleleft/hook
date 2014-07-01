<?php
namespace API\PushNotification\Services;

use API\Model\AppConfig as AppConfig;

/**
 * @nodoc
 */
class APNSLogger implements \ApnsPHP_Log_Interface
{
    public function log($message)
    {
        $app = \Slim\Slim::getInstance();
        $app->log->info($message);
    }
}

class APNS implements Service
{
    /**
     * push
     * @param mixed $registrations
     * @param mixed $data
     */
    public function push($registrations, $data)
    {
        $apns_environment = AppConfig::get('push.apns.environment', 'sandbox');
        $apns_certificate_file = AppConfig::get('push.apns.cert.file', false);
        $apns_certificate_pass = AppConfig::get('push.apns.cert.pass', false);

        if (!$apns_certificate_file) {
            throw new \Exception("APNS config error: 'push.apns.cert.file' not set.");
        }

        $app = \Slim\Slim::getInstance();
        $total_failure = 0;

        // Instantiate a new ApnsPHP_Push object
        $push = new \ApnsPHP_Push(
            ($apns_environment == 'sandbox') ? \ApnsPHP_Abstract::ENVIRONMENT_SANDBOX : \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
            $this->getCertificateFile($apns_certificate_file)
        );

        // set custom logger
        $push->setLogger(new APNSLogger());

        // Set the Provider Certificate passphrase
        if ($apns_certificate_pass) {
            $push->setProviderCertificatePassphrase($apns_certificate_pass);
        }

        // Set the Root Certificate Autority to verify the Apple remote peer
        $push->setRootCertificationAuthority($this->getRootCertificationAuthority());

        // Connect to the Apple Push Notification Service
        $push->connect();

        $message = new \ApnsPHP_Message();

        // Add all registrations as message recipient
        foreach ($registrations as $registration) {
            try {
                $message->addRecipient($registration->device_id);
            } catch (\ApnsPHP_Message_Exception $e) {
                $app->log->info($e->getMessage());
                $total_failure +=1;
            }
        }

        debug("Recipients => " . json_encode($message->getRecipients()));

        // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
        // over a ApnsPHP_Message object retrieved with the getErrors() message.
        if (isset($data['custom_identifier'])) {
            $message->setCustomIdentifier($data['custom_identifier']);
        }

        // Set badge icon to "3"
        if (isset($data['badge']) && is_int($data['badge'])) {
            $message->setBadge((int) $data['badge']);
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
        foreach ($custom_properties as $property => $value) {
            $message->setCustomProperty($property, $value);
        }

        // Add the message to the message queue
        $push->add($message);

        // Send all messages in the message queue
        $stats = $push->send();

        // Disconnect from the Apple Push Notification Service
        $push->disconnect();

        // Examine the error message container
        $error_list = $push->getErrors();

        // Log delivery status
        $errors = $push->getErrors();
        $total_failure += count($errors);

        if ($total_failure > 0) {
            foreach ($errors as $error) {
                $app->log->info($errors);
            }
        }

        return array(
            'success' => $registrations->count() - $total_failure,
            'failure' => $total_failure
        );
    }

    /**
     * getCertificateFile
     * @param string $contents
     */
    protected function getCertificateFile($contents)
    {
        $filename = storage_dir() . '/' . md5($contents) . '.pem';

        if (!file_exists($filename)) {
            file_put_contents($filename, $contents);
        }

        return realpath($filename);
    }

    protected function getRootCertificationAuthority()
    {
        $filename = storage_dir() . '/' . md5('entrust_2048_ca') . '.cer';

        if (!file_exists($filename)) {
            file_put_contents($filename, file_get_contents('https://www.entrust.net/downloads/binary/entrust_2048_ca.cer'));
        }

        return realpath($filename);
    }

}
