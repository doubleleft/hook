<?php namespace Hook\Mailer;

use Hook\Application\Config;
use Hook\Model\Module as Module;
use Hook\View\MailHelper;

use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;

/**
 * Mail delivery class
 *
 */
class Mail
{
    // Cached Mailer instance
    protected static $_mailer;

    /**
     * send
     *
     * @example Send email using SMTP
     *
     *     Mail::send(array(
     *         'to' => "to@email.com",
     *         'from' => "from@email.com",
     *         'body' => "Email body",
     *         'subject' => "Your email subject"
     *     ));
     *
     * @param array $options
     */
    public static function send($message)
    {
        if (is_array($message)) {
            $message = new Message($message);
        }

        return static::getMailer()->send($message->getOriginal());
    }

    /**
     * Create a new Message.
     *
     * @param string $template_body path or data to be attached
     * @param string $options
     *
     * @return Swift_Mime_Attachment
     */
    public static function message($template_body = null, $options = array()) {
        $message = new Message();
        MailHelper::setMessage($message);

        $template = Module::template($template_body);
        $message->body($template->compile($options));

        return $message;
    }

    /**
     * Create a new Attachment.
     *
     * @param string|Swift_OutputByteStream $path_or_data   path or data to be attached
     * @param string                        $filename
     * @param string                        $contentType
     *
     * @return Swift_Mime_Attachment
     */
    public static function attachment($path_or_data = null, $filename = null, $contentType = null) {
        $from_path = realpath($path_or_data);
        if ($from_path) {
            $attachment = Swift_Attachment::fromPath($path_or_data, $contentType);
            $attachment->setFilename($filename);
        } else {
            $attachment = Swift_Attachment::newInstance($path_or_data, $filename, $contentType);
        }
        return $attachment;
    }

    public static function getMailer()
    {
        if (!static::$_mailer) {
            static::$_mailer = Swift_Mailer::newInstance(static::getTransport());
        }

        return static::$_mailer;
    }

    public static function getTransport()
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

        $transport_klass = '\Swift_'.ucfirst(strtolower($params['driver'])).'Transport';
        $transport = call_user_func(array($transport_klass, 'newInstance'));
        unset($params['driver']);

        // Set custom transport params
        foreach ($params as $param => $value) {
            call_user_func(array($transport, 'set' . ucfirst($param)), $value);
        }

        return $transport;
    }

}
