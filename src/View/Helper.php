<?php namespace Hook\View;

use Hook\Http\Router;
use Hook\Http\Request;

class Helper {

    //
    // Core helpers
    //

    public static function yieldContent($args) {
        $content = isset($args[0]) ? $args[0] : '__yield__';
        $yield_blocks = Router::getInstance()->view->yield_blocks;
        return array(isset($yield_blocks[$content]) ? $yield_blocks[$content] : "", 'raw');
    }

    //
    // String helpers
    //

    public static function lowercase($args) {
        return strtolower($string[0]);
    }

    public static function uppercase($args) {
        return strtoupper($string[0]);
    }

    public static function str_singular($args) {
        return str_singular($args[0]);
    }

    public static function str_plural($args) {
        return str_plural($args[0]);
    }

    public static function snake_case($args) {
        return snake_case($args[0]);
    }

    public static function camel_case($args) {
        return camel_case($args[0]);
    }

    //
    // URL helpers
    //

    public static function link_to($args, $attributes) {
        $text = (isset($args[1])) ? $args[1] : $args[0];

        $app_key = \Hook\Application\Context::getKey();
        $public_url = public_url($args[0]) . '?X-App-Id=' . $app_key->app_id . '&X-App-Key=' . $app_key->key;

        return array('<a href="'. $public_url .'"' . html_attributes($attributes) . '>' . $text . '</a>', 'raw');
    }

    public static function stylesheet($args, $attributes) {
        $url = preg_replace('/index\.php\//', '', str_finish(Request::getRootUri(), '/')) . $args[0];
        $media = (isset($attributes['media'])) ? $attributes['media'] : 'screen';
        return array('<link href="' . $url . '" media="' . $media . '" rel="stylesheet" />', 'raw');
    }

    public static function javascript($args, $attributes) {
        $url = preg_replace('/index\.php\//', '', str_finish(Request::getRootUri(), '/')) . $args[0]; // Request::getRootUri()
        return array('<script src="' . $url . '"></script>', 'raw');
    }

    //
    // Form helpers
    //

    public static function input($args, $attributes) {
        if (!isset($attributes['name']) && isset($args[0])) {
            // TODO: analyse context recursively
            if (Router::getInstance()->view->context->count() > 0) {
                $attributes['name'] = Router::getInstance()->view->context->top() . '['.$args[0].']';
            } else {
                $attributes['name'] = $args[0];
            }
        }

        if (isset($attributes['options'])) {
            return \Hook\Framework\Helper::select($args, $attributes);
        }

        // use 'text' as default input type
        if (!isset($attributes['type'])) {
            $is_type_as_name = in_array($attributes['name'], array('email', 'password', 'date'));
            $attributes['type'] = $is_type_as_name ? $attributes['name'] : 'text';
        }

        return array('<input' . html_attributes($attributes) . ' />', 'raw');
    }

    public static function select($args, $attributes) {
        $options = array_remove($attributes, 'options');
        $selected_option = array_remove($attributes, 'selected');

        if (!isset($attributes['name']) && isset($args[0])) {
            // TODO: analyse context recursively
            if (Router::getInstance()->view->context->count() > 0) {
                $attributes['name'] = Router::getInstance()->view->context->top() . '['.$args[0].']';
            } else {
                $attributes['name'] = $args[0];
            }
        }

        $html_options = '';
        foreach($options as $key => $value) {
            $key = isset($value['_id']) ? $value['_id'] : $key;
            $value = isset($value['name']) ? $value['name'] : $value;
            $is_selected = ($selected_option == $key) ? ' selected="selected"' : '';
            $html_options .= '<option value="' . $key . '"' . $is_selected . '>' . $value . '</option>';
        }

        return array('<select' . html_attributes($attributes) . '>'.$html_options.'</select>', 'raw');
    }

    //
    // Data helpers
    //

    public static function count($args) {
        return count($args[0]);
    }

    public static function config($args) {
        return \Hook\Application\Config::get($args[0]);
    }

    //
    // Miscelaneous helpers
    //

    public static function paginate($args, $named) {
        $collection = $args[0];

        if (!method_exists($collection, 'links')) {
            return "paginate: must have 'links' method.";
        }

        // pagination window
        if (isset($named['window'])) {
            $collection->getEnvironment()->setPaginationWindow($named['window']);
        }

        return array($args[0]->links(), 'raw');
    }

}
