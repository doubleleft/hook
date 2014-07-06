<?php
namespace API\Database;

use API\Model\App as App;
use API\Database\Schema\Cache as Cache;

/**
 * Relationship
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Relationship
{
    static $types = array(
        'belongs_to' => '\Illuminate\Database\Eloquent\Relations\BelongsTo',
        'belongs_to_many' => '\Illuminate\Database\Eloquent\Relations\BelongsToMany',
        'has_one' => '\Illuminate\Database\Eloquent\Relations\HasOne',
        'has_one_or_many' => '\Illuminate\Database\Eloquent\Relations\HasOneOrMany',
        'has_many' => '\Illuminate\Database\Eloquent\Relations\HasMany',
    );

    public static function getRelation($model, $relation_name) {
        $schema = Cache::get($model->getTable());
        $debug = json_encode($schema);

        if (isset($schema['relationships'])) {
            // TODO: refactoring.
            // change the way to store relationships to prevent excessive loops
            foreach($schema['relationships'] as $relation => $fields) {
                foreach($fields as $field => $collection) {
                    if ($field == $relation_name) {

                        $related = App::collection($collection);
                        return static::getRelationInstance($model, $related, $relation, $field);
                    }
                }
            }
        }

        return null;
    }

    /**
     * get
     *
     * @param mixed $object
     * @param string $relation_name
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public static function getRelationInstance($object, $related, $relation_type, $field) {
        $relation = str_singular($field);
        $foreign_key = $relation . '_id';
        $other_key = $related->getModel()->getKeyName();
        $query = $related->getQueryBuilder();
        return new \Illuminate\Database\Eloquent\Relations\BelongsTo($query, $object, $foreign_key, $other_key, $relation);
    }

}
