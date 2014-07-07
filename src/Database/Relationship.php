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

                        $related_collection = App::collection($collection);
                        return static::getRelationInstance($model, $related_collection, $relation, $field);
                    }
                }
            }
        }

        return null;
    }

    /**
     * get
     *
     * @param mixed $model
     * @param string $relation_name
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public static function getRelationInstance($model, $related_collection, $relation_type, $field) {
        $relation_name = null;
        $relation_klass = static::$types[$relation_type];
        $related_model = $related_collection->getModel();
        $related_table = $related_model->getTable();

        switch ($relation_type) {
        case "belongs_to":
            $relation_name = str_singular($field);
            $foreign_key = $relation_name . '_id';
            $other_key = $related_model->getKeyName();
            $query = $related_collection->getQueryBuilder();
            $relation = new $relation_klass($query, $model, $foreign_key, $other_key, $relation_name);
            break;

        case "belongs_to_many":
            $relation_name = str_singular($field);
            $foreign_key = $relation_name . '_id';
            $other_key = $related_collection->getModel()->getKeyName();
            $query = $related_collection->getQueryBuilder();
            $relation = new $relation_klass($query, $model, $related_model, $related_table, $foreign_key, $other_key, $relation_name);
            // __construct(Builder $query, Model $parent, $table, $foreignKey, $otherKey, $relationName = null)
            break;

        case "has_many":
        case "has_one":
        case "has_one_or_many":
            $relation_name = str_singular($field);
            $foreign_key = $relation_name . '_id';
            $other_key = $related_collection->getModel()->getKeyName();
            $query = $related_collection->getQueryBuilder();
            $relation = new $relation_klass($query, $related_model, $foreign_key, $other_key);
            // __construct(Builder $query, Model $parent, $foreignKey, $localKey)
            break;

        }

        return $relation;
    }

}
