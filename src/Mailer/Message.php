<?php namespace Hook\Mailer;

use Hook\Model\Module;
use Swift_Message;

/**
 * Proxy to Swift_Message class
 *
 */
class Message
{
    public function __construct($options = array()) {
        $this->message = new Swift_Message();

        if (!empty($options)) {
            foreach ($options as $method => $argument) {
                call_user_func_array(array($this, $method), array($argument));
            }
        }
    }

    /**
     * Get original swiftmailer message instance
     *
     * @return Swift_Message
     */
    public function getOriginal() {
        return $this->message;
    }

    /**
     * Set the body content of this entity as a string.
     *
     * @param string $body
     * @param string $contentType optional (default: 'text/html')
     */
    public function body($body, $contentType = 'text/html') {
        $this->message->setBody($body, $contentType);

        return $this;
    }

    /**
     * Set the Content-type of this entity.
     *
     * @param string $type
     *
     * @return Message
     */
    public function contentType($type) {
        $this->message->setContentType($type);

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return Message
     */
    public function subject($subject) {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Set the origination date of the message as a UNIX timestamp.
     *
     * @param integer $date
     *
     * @return Message
     */
    public function date($date) {
        $this->message->setDate($date);

        return $this;
    }

    /**
     * Set the return-path (bounce-detect) address.
     *
     * @param string $address
     *
     * @return Message
     */
    public function returnPath($address) {
        $this->message->setReturnPath($address);

        return $this;
    }

    /**
     * Set the From address of this message.
     *
     * It is permissible for multiple From addresses to be set using an array.
     *
     * If multiple From addresses are used, you SHOULD set the Sender address and
     * according to RFC 2822, MUST set the sender address.
     *
     * An array can be used if display names are to be provided: i.e.
     * array('email@address.com' => 'Real Name').
     *
     * If the second parameter is provided and the first is a string, then $name
     * is associated with the address.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return Message
     */
    public function from($addresses, $name = null) {
        $this->message->setFrom($addresses, $name);

        return $this;
    }

    /**
     * Set the Reply-To address(es).
     *
     * Any replies from the receiver will be sent to this address.
     *
     * It is permissible for multiple reply-to addresses to be set using an array.
     *
     * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
     *
     * If the second parameter is provided and the first is a string, then $name
     * is associated with the address.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return Message
     */
    public function replyTo($addresses, $name = null) {
        $this->message->setReplyTo($addresses, $name);

        return $this;
    }

    /**
     * Set the To address(es).
     *
     * Recipients set in this field will receive a copy of this message.
     *
     * This method has the same synopsis as {@link setFrom()} and {@link setCc()}.
     *
     * If the second parameter is provided and the first is a string, then $name
     * is associated with the address.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return Message
     */
    public function to($addresses, $name = null) {
        $this->message->setTo($addresses, $name);

        return $this;
    }

    /**
     * Set the Cc address(es).
     *
     * Recipients set in this field will receive a 'carbon-copy' of this message.
     *
     * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return Message
     */
    public function cc($addresses, $name = null) {
        $this->message->setCc($addresses, $name);
        return $this;
    }

    /**
     * Set the Bcc address(es).
     *
     * Recipients set in this field will receive a 'blind-carbon-copy' of this
     * message.
     *
     * In other words, they will get the message, but any other recipients of the
     * message will have no such knowledge of their receipt of it.
     *
     * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return Message
     */
    public function bcc($addresses, $name = null) {
        $this->message->setBcc($addresses, $name);
        return $this;
    }

    /**
     * Attach a {@link Swift_Mime_MimeEntity} such as an Attachment or MimePart.
     *
     * @param Swift_Mime_MimeEntity|string $path_or_entity
     * @param string $filename
     * @param string $contentType
     *
     * @return Message
     */
    public function attach($path_or_entity = null, $filename = null, $contentType = null) {
        if ($path_or_entity instanceof \Swift_Mime_MimeEntity) {
            $this->message->attach($path_or_entity);
        } else {

            $attachment = Mail::attachment($path_or_entity, $filename, $contentType);
            $this->message->attach($attachment);
        }

        return $this;
    }

    /**
     * Send the message using
     *
     * @param boolean $sent
     */
    public function send() {
        return Mail::send($this);
    }

    //
    // Method not implemented in the proxy, let's call proxied instance method
    //
    public function __call($method, $arguments) {
        return call_user_func_array(array($this->message, $method), $arguments);
    }

}
