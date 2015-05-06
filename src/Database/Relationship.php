<?php namespace Hook\Database;

use Hook\Exceptions\NotImplementedException;

use Hook\Model\App as App;
use Hook\Database\Schema\Cache as Cache;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Hook\Database\Relations\HasManyThrough;

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
            foreach($schema['relationships'] as $relation_type => $fields) {
                foreach($fields as $field => $config) {
                    if ($field == $relation_name || $config['collection'] == $relation_name) {
                        return static::getRelationInstance($model, $relation_type, $field, $config);
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
    public static function getRelationInstance($model, $relation_type, $field, $config) {
        $related_collection = App::collection($config['collection']);
        $model_table = str_singular($model->getTable());

        $related_model = $related_collection->getModel();
        $related_table = $related_model->getTable();

        $primary_key = $config['primary_key'];
        $foreign_key = $config['foreign_key'];

        // define relation model
        $related_klass = "Related" .
            ucfirst( str_singular( camel_case( $field ) ) ) .
            ucfirst( str_singular( camel_case( $related_table ) ) );

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
            return new BelongsTo($related_query, $model, $foreign_key, $primary_key, $field);

        case "belongs_to_many":
            return new BelongsToMany($related_query, $model, $related_table, $foreign_key, $primary_key, $field);

        case "has_many":
            // hasMany($related, $foreignKey = null, $localKey = null)
            // $model->hasMany($related_klass, $model_table . '_id', '_id');

            if (isset($config['through'])) {
                $through = App::collection($config['through'])->getModel();

                file_put_contents('php://stdout', $through->getTable() . "\n");
                file_put_contents('php://stdout', $through->getQualifiedKeyName() . "\n");
                file_put_contents('php://stdout', $model->getTable() . "\n");
                file_put_contents('php://stdout', $model->getQualifiedKeyName() . "\n");

                //          __construct(Builder $query, Model $farParent, Model $parent, $firstKey, $secondKey)
                // return new HasManyThrough(with(new $related)->newQuery(), $this, $through, $firstKey, $secondKey);
                return new HasManyThrough($related_query, $model, $through, 'book_id', 'author_id');

            } else {
                return new HasMany($related_query, $model, $foreign_key, $primary_key);
            }

        case "has_one":
            // hasOne($related, $foreignKey = null, $localKey = null)
            // $model->hasOne($related_klass, $model_table . '_id', '_id');
            return new HasOne($related_query, $model, $foreign_key, $primary_key);

        default:
            return new NotImplementedException("'{$relation_type}' is not implemented. Please use 'belongs_to', 'has_many' or 'belongs_to_many'.");

        }

        return null;
    }

}
