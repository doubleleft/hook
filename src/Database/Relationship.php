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

    public static function getRelation($model, $relation_name) {
        $schema = Cache::get($model->getTable());
        $debug = json_encode($schema);

        if (isset($schema['relationships'])) {
            // TODO: refactoring.
            // change the way to store relationships to prevent excessive loops
            foreach($schema['relationships'] as $relation => $fields) {
                foreach($fields as $field => $collection) {
                    if ($field == $relation_name || $collection == $relation_name) {
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
        $model_table = str_singular($model->getTable());

        $related_model = $related_collection->getModel();
        $related_table = $related_model->getTable();
        $foreign_key = $field . '_id';

        // define relation model
        $related_klass = "Related" . ucfirst( str_singular( camel_case( $related_collection->getTable() ) ) );

        // TODO: refactoring
        // eval is evil. But it's necessary here since Eloquent\Model
        // will try to instantiate the 'related class' without constructor params.
        if (!class_exists($related_klass)) {
            eval("class {$related_klass} extends API\Model\Collection { protected \$table = '{$related_table}'; }");
        }

        switch ($relation_type) {
        case "belongs_to":
            // belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
            return $model->belongsTo($related_klass, $foreign_key, '_id', $field);

        case "belongs_to_many":
            // belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
            return $model->belongsToMany($related_klass, $related_table, $foreign_key, '_id', $field);

        case "has_many":
            // hasMany($related, $foreignKey = null, $localKey = null)
            return $model->hasMany($related_klass, $model_table . '_id', '_id');

        case "has_one":
            // hasOne($related, $foreignKey = null, $localKey = null)
            return $model->hasOne($related_klass, $model_table . '_id', '_id');

        case "has_many_through":
            // hasManyThrough('Post', 'User', 'country_id', 'user_id');
            return $model->hasManyThrough($related_klass, $foreign_key, '_id');
        }

        return null;
    }

}
