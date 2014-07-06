<?php namespace API\Database\Schema;

class Relation
{

    public static function sanitize($relation, $config)
    {
        $is_singular = !preg_match('/_many/', $relation);
        $fields = array();

        if (is_array($config)) {
            foreach($config as $field => $collection) {
                if (is_array($collection)) {
                    $field = key($collection);
                    $collection = current($collection);
                }

                // collection names are always plural
                $collection = str_plural($collection);

                // field name not specified. use default pattern.
                if (is_int($field)) {
                    $field = ($is_singular) ? str_singular($collection) : $collection;
                }

                $fields[$field] = $collection;
            }

        } else {
            $fields[ str_singular($config) ] = str_plural($config);
        }

        return $fields;
    }

}
