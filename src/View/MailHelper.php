<?php namespace Hook\View;

use Swift_Image;

class MailHelper {
    static $message;

    public static function setMessage($message) {
        static::$message = $message;
    }

    //
    // Mail helpers
    //

    public static function embed_img($args, $attributes = null) {
        $cid = \Hook\View\MailHelper::embed($args);

        return array('<img src="' . $cid . '"' . html_attributes($attributes) . ' />', 'raw');
    }

    public static function embed($args, $attributes = null) {
        $image = \Swift_Image::fromPath($args[0]);

        $cid = static::$message->embed($image);

        return $cid;
    }

}

