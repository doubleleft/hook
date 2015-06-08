<?php namespace Hook\View;

class MailHelper {
    static $message;

    public static function setMessage($message) {
        static::$message = $message;
    }

    //
    // Mail helpers
    //

    public static function img_embed($args, $attributes) {
        $cid = \Hook\View\MailHelper::embed($args, $options['hash']);

        if (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }

        return array('<img src="' . $cid . '" alt="' . $attributes['alt'] . '" />', 'raw');
    }

    public static function embed($args, $attributes) {
        $cid = \Swift_Image::fromPath($args[0]);

        return $cid;
    }

}

