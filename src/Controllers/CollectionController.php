<?php namespace Hook\Controllers;

use Hook\Application\Context;

use Hook\Model;
use Hook\Http\Input;
use Hook\Http\Response;
use Hook\Http\Request;

use Hook\Database\CollectionDelegator;

class CollectionController extends HookController {

    public function index($name) {
        $query = Model\App::collection($name)->filter(Input::get('q'));

        // Select fieds
        if ($fields = Input::get('select')) {
            $query = $query->select($fields);
        }

        // Distinct keyword
        if ($distinct = Input::get('distinct')) {
            $query = $query->distinct();
        }

        // Apply ordering
        if ($s = Input::get('s', array(array('_id', 'asc')))) {
            foreach ($s as $ordering) {
                $query->orderBy(reset($ordering), end($ordering));
            }
        }

        // Apply group
        if ($group = Input::get('g')) {
            foreach ($group as $field) {
                $query = $query->groupBy($field);
            }
        }

        $offset = Input::get('offset');
        $limit = Input::get('limit');

        //
        // Append total rows if performing a pagination
        //
        // FIXME: We should use more elegant solution here with headers:
        // 'Range', 'Accept-Ranges' and 'Content-Range'
        //
        if ($limit !== NULL && $offset !== NULL) {
            Response::header('Access-Control-Expose-Headers', 'X-Total-Count');
            Response::header('X-Total-Count', $query->count());
        }

        // limit / offset
        if ($limit) { $query = $query->take($limit); }
        if ($offset) { $query = $query->skip($offset); }

        // remember / caching
        if ($remember = Input::get('remember')) {
            $query = $query->remember($remember);
        }

        // with - eager load relationships
        if ($with = Input::get('with')) {
            $query = call_user_func_array(array($query, 'with'), $with);
        }

        if ($aggr = Input::get('aggr')) {
            // Aggregate 'max'/'min'/'avg'/'sum' methods
            if (isset($aggr['field'])) {
                $aggregate = $query->{$aggr['method']}($aggr['field']);

            } else {
                // Aggregate 'count'
                $aggregate = $query->{$aggr['method']}();
            }

            Response::header('Content-type', 'application/json');
            Response::setBody( to_json($aggregate) );
            return true;

        } elseif ($paginate = Input::get('p')) {
            // Apply pagination
            return $query->paginate($paginate);

        } elseif (Input::get('f')) {
            // First
            return $query->first();

        } else {
            return $query->get();
        }
    }

    //
    // POST /collection/:name
    //
    public function store($name) {
        $collection = Model\App::collection($name);

        $method = (Input::get('f')) ? 'firstOrCreate' : 'create';
        $model = call_user_func(array($collection, $method), static::getData());

        // TODO: DRY with 'index' method
        // with - eager load relationships
        if ($with = Input::get('with')) {
            return CollectionDelegator::queryEagerLoadRelations($model, $with);
        } else {
            return $model;
        }
    }

    //
    // PUT /collection/:name
    //
    public function put($name, $_id = null) {
        $collection = Model\App::collection($name);
        $query = ($_id) ? $collection->find($_id) : $collection->filter(Input::get('q'));

        if ($operation = Input::get('op')) {
            // Operations: increment/decrement
            return $query->{$operation['method']}($operation['field'], $operation['value']);
        } else {

            // Perform raw update
            $affected = (int) $query->update(static::getData());
            return array(
                'success' => $affected > 0,
                'affected' => $affected
            );
        }
    }

    public function post($name, $_id) {
        $collection = Model\App::collection($name);
        if ($model = $collection->find($_id)) {
            if ($model->fill(static::getData()) && $model->isModified()) {
                if (!$model->save()) {
                    throw new ForbiddenException("Can't save '{$collection->getName()}'.");
                }
            }
        }
        return $model;
    }

    public function show($name, $_id) {
        return Model\App::collection($name)->find($_id);
    }

    public function delete($name, $_id = null) {
        $collection = Model\App::collection($name);
        $success = false;

        // trusted context:
        // run a real truncate statement if performing a delete
        if (Context::isTrusted() && $_id == null && count(Input::get('q')) == 0) {
            $success = $collection->truncate();

        } else {
            // untrusted context:
            // remove a single row, or the items from a filter in
            $query = ($_id) ? $collection->find($_id) : $collection->filter(Input::get('q'));
            $success = $query->delete();
        }

        return array('success' => $success);
    }

    public static function getData() {
        // TODO: refactoring
        if (Request::isPost()) {
            $data = Request::post('d', Request::post('data', Request::post()));
        } else {
            $data = Input::get('d', Input::get('data', Input::get()));
        }

        $attached_files = array();

        // Check for base64-encoded files
        foreach ($data as $key => $value) {
            if (Model\File::base64($value)) {
                $attached_files[$key] = $value;
            }
        }

        if (!empty($_FILES)) {
            $attached_files = array_merge($attached_files, $_FILES);
        }

        if (!empty($attached_files)) {
            $data[Model\Collection::ATTACHED_FILES] = $attached_files;
        }

        return $data;
    }
}
