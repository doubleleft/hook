<?php
namespace Hook\Mailer;

use Hook\Application\Config;
use Hook\Model\App as App;

/**
 * Mail delivery class
 *
 */
class Mail
{
    /**
     * send
     *
     * @example Send email using SMTP
     *
     *     Mail::send(array(
     *         'to' => "somebody@gmail.com",
     *         'to' => "somebody@gmail.com",
     *         'to' => "somebody@gmail.com",
     *         'to' => "somebody@gmail.com"
     *     ));
     *
     * @param array $options
     */
    public static function send($options = array())
    {
        $params = array();
        $allowed_configs = array('driver', 'host', 'port', 'encryption', 'username', 'password');

        foreach(Config::get('mail', array()) as $name => $value) {
            if (in_array($name, $allowed_configs)) {
                $params[$name] = $value;
            }
        }

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

    protected static function getTransport($params = array())
    {
        // Validate SMTP params
        if ($params['driver'] == 'smtp' && (!isset($params['username']) || !isset($params['password']))) {
            throw new Exception("'mail.username' and 'mail.password' configs are required when using 'smtp' driver;");
        }

        $transport_klass = '\Swift_'.ucfirst(strtolower($params['driver'])).'Transport';
        $transport = call_user_func(array($transport_klass, 'newInstance'));
        unset($params['driver']);

        // Set custom transport params
        foreach ($params as $param => $value) {
            call_user_func(array($transport, 'set' . ucfirst($param)), $value);
        }

        return $transport;
    }

    protected static function sendMessage($transport, $options)
    {
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

}
