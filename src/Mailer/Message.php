<?php namespace Hook\Mailer;

use Hook\Model\Module;
use Swift_Message;

/**
 * Proxy to Swift_Message class
 *
 */
class Message
{
    public function __construct() {
        $this->message = new Swift_Message();
    }

    /**
     * Set the body content of this entity as a string.
     *
     * @param string $body
     * @param string $contentType optional
     */
    public function body($body, $contentType = null) {
        $this->message->setBody($body, $contentType);

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     */
    public function subject($subject) {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Set the origination date of the message as a UNIX timestamp.
     *
     * @param integer $date
     */
    public function date($date) {
        $this->message->setDate($date);

        return $this;
    }

    /**
     * Set the return-path (bounce-detect) address.
     *
     * @param string $address
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
     */
    public function bcc($addresses, $name = null) {
        $this->message->setBcc($addresses, $name);
        return $this;
    }

    public function attach($path_or_data = null, $filename = null, $contentType = null) {
        if ($path_or_data instanceof \Swift_Mime_MimeEntity) {
            $this->message->attach($path_or_data);
        } else {

            $attachment = Mail::attachment($path_or_data, $filename, $contentType);
            $this->message->attach($attachment);
        }

        return $this;
    }

    //
    // Method not implemented in the proxy, let's call proxied instance method
    //
    public function __call($method, $arguments) {
        return call_user_func_array(array($this->message, $method), $arguments);
    }

}
