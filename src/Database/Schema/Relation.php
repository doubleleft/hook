<?php namespace Hook\Database\Schema;

class Relation
{

    public static function sanitize($collection_name, $relation_type, $configs)
    {
        if (!is_array($configs)) { $configs = array($configs); }

        $is_singular = !preg_match('/_many/', $relation_type);
        $fields = array();

        foreach($configs as $config) {
            if (is_string($config)) {
                $field = $config; // field name not specified.
                $config = array('collection' => $config);

            } elseif (is_array($config)) {
                $field = key($config);
                $config = current($config);

            } else {
                throw new \Exception("Invalid relation configuration.");
            }

            // pluralize if needed by relation_type
            $field = ($is_singular) ? str_singular($field) : str_plural($field);

            $fields[$field] = static::getEntityConfig($relation_type, $collection_name, $field, $config);
        }

        return $fields;
    }

    protected static function getEntityConfig($relation_type, $collection_name, $field, $config) {
        // collection names are always plural
        $collection = array_remove($config, 'collection') ?: $field;
        $config['collection'] = str_plural($collection);

        $foreign_key = array_remove($config, 'foreign_key');
        if (!$foreign_key) {
            $foreign_key = ($relation_type == 'has_many')
               ? str_singular($collection_name) . '_id'
               : str_singular($field) . '_id';
        }
        $config['foreign_key'] = $foreign_key;
        $config['primary_key'] = array_remove($config, 'primary_key') ?: '_id';

        if ($relation_type == 'belongs_to') {
            // belongs_to relation types have the following additional config keys:
            $config['required'] = array_remove($config, 'required') ?: false;
            $config['on_delete'] = array_remove($config, 'on_delete') ?: 'none';
            $config['on_update'] = array_remove($config, 'on_update') ?: 'none';
        }

        return $config;
    }

}
