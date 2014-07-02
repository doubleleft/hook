<?php
namespace API\Database;

use API\Model\App as App;

/**
 * Relationship
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Relationship
{

    public static function exists($table, $relation_name) {
        return false;

        if (str_singular($relation_name) == 'author') {

        }
    }

    /**
     * get
     *
     * @param mixed $object
     * @param string $relation_name
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public static function get($object, $relation_name) {
        if ($relation_name == 'authors') {
            $related = App::collection('authors');
            $relation = str_singular($relation_name);
            $foreign_key = $relation . '_id';
            $other_key = $related->getModel()->getKeyName();
            $query = $related->getQueryBuilder();
            return new \Illuminate\Database\Eloquent\Relations\BelongsTo($query, $object, $foreign_key, $other_key, $relation);
        }
    }

}
