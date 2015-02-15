<?php
namespace Hook\Database;

use Hook\Exceptions\NotImplementedException;

use Hook\Model\App as App;
use Hook\Database\Schema\Cache as Cache;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Relationship
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class Relationship
{

    public static function getRelation($model, $relation_name) {
        $schema = Cache::get($model->getTable());

        if (isset($schema['relationships'])) {
            // TODO: refactoring.
            // change the way to store relationships to prevent excessive loops
            foreach($schema['relationships'] as $relation => $fields) {
                foreach($fields as $field => $collection) {
                    if ($field == $relation_name || $collection == $relation_name) {
                        // TODO: '$collection' should be always a string
                        $related_collection_name = (is_array($collection)) ? $collection[0] : $collection;
                        $related_collection = App::collection($related_collection_name);
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
        $related_klass = "Related" .
            ucfirst( str_singular( camel_case( $model_table ) ) ) .
            ucfirst( str_singular( camel_case( $related_collection->getTable() ) ) );

        // FIXME:
        // eval is evil. But it's necessary here since Eloquent\Model
        // will try to instantiate the 'related class' without constructor params.
        if (!class_exists($related_klass)) {
            $related_model_class = get_class($related_model);
            eval("class {$related_klass} extends {$related_model_class} { protected \$table = '{$related_table}'; }");
        }

        // FIXME: refactoring
        // force table name on related query instance.
        $related_instance = new $related_klass;
        $related_instance->getModel()->setTable($related_table);
        $related_query = $related_instance->getModel()->newQuery();

        switch ($relation_type) {
        case "belongs_to":
            // $model->belongsTo($related_klass, $foreign_key, '_id', $field);
            return new BelongsTo($related_query, $model, $foreign_key, '_id', $field);

        case "belongs_to_many":
            // $model->belongsToMany($related_klass, $related_table, $foreign_key, '_id', $field);
            return new BelongsToMany($related_query, $model, $related_table, $foreign_key, '_id', $field);

        case "has_many":
            // hasMany($related, $foreignKey = null, $localKey = null)
            // $model->hasMany($related_klass, $model_table . '_id', '_id');
            return new HasMany($related_query, $model, $model_table . '_id', '_id');

        case "has_one":
            // hasOne($related, $foreignKey = null, $localKey = null)
            // $model->hasOne($related_klass, $model_table . '_id', '_id');
            return new HasOne($related_query, $model, $model_table . '_id', '_id');

        case "has_many_through":
            // hasManyThrough('Post', 'User', 'country_id', 'user_id');
            return new NotImplementedException("has_many_through not implemented.");
        }

        return null;
    }

}
