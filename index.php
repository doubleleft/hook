<?php
$app = require __DIR__ . '/src/Hook.php';

use Hook\Middlewares as Middlewares;
use Hook\Database\AppContext as AppContext;
use Hook\Database\Schema as Schema;
use Hook\PushNotification as PushNotification;
use Hook\Cache\Cache as Cache;

use Hook\Model as Model;
use Hook\Auth as Auth;

use Hook\Exceptions\ForbiddenException as ForbiddenException;
use Carbon\Carbon as Carbon;

// Middlewares
$app->add(new Middlewares\ResponseTypeMiddleware());
$app->add(new Middlewares\LogMiddleware());
$app->add(new Middlewares\AppMiddleware());

// Attach user authentication
$app->add(new Middlewares\AuthMiddleware());

$app->get('/', function () use ($app) {
    $app->content = Model\App::all();
});

/**
 * Misc system utilities
 */
$app->group('/system', function () use ($app) {
    /**
     * GET /system/time
     */
    $app->get('/time', function () use ($app) {
        $app->content = time();
    });

    /**
     * GET /system/ip
     */
    $app->get('/ip', function () use ($app) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_REAL_IP"])) {
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        }
        $app->content = json_decode(file_get_contents("http://ipinfo.io/$ip/json"), true);
    });
});

/**
 * Collection routes
 */
$app->group('/collection', function () use ($app) {

    // Get collection data for CREATE/UPDATE
    $app->container->singleton('collection_data', function () use ($app) {
        //
        // TODO: android and ios clients should deprecate 'data' param, and send it entirelly on BODY
        //
        $data = $app->request->post('d') ?: $app->request->post('data') ?: $app->request->post();

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
    });

    /**
     * GET /collection/:name
     */
    $app->get('/:name', function ($name) use ($app) {
        $query = Model\App::collection($name)->filter($app->request->get('q'));

        // Apply ordering
        if ($s = $app->request->get('s')) {
            foreach ($s as $ordering) {
                $query->orderBy(reset($ordering), end($ordering));
            }
        }

        // Apply group
        if ($group = $app->request->get('g')) {
            foreach ($group as $field) {
                $query = $query->groupBy($field);
            }
        }

        // limit / offset
        if ($offset = $app->request->get('offset')) {
            $query = $query->skip($offset);
        }

        if ($limit = $app->request->get('limit', 1000)) {
            $query = $query->take($limit);
        }

        // remember / caching
        if ($remember = $app->request->get('remember')) {
            $query = $query->remember($remember);
        }

        // with - eager load relationships
        if ($with = $app->request->get('with')) {
            $query = call_user_func_array(array($query, 'with'), $with);
        }

        if ($aggr = $app->request->get('aggr')) {
            // Aggregate 'max'/'min'/'avg'/'sum' methods
            if ($aggr['field']) {
                $app->content = $query->{$aggr['method']}($aggr['field']);
            } else {
                // Aggregate 'count'
                $app->content = $query->{$aggr['method']}();
            }

        } elseif ($app->request->get('p')) {
            // Apply pagination
            $app->content = $query->paginate($app->request->get('p'));

        } elseif ($app->request->get('f')) {
            // First
            $app->content = $query->first();

        } else {
            $app->content = $query->get();
        }

    });

    /**
     * POST /collection/:name
     */
    $app->post('/:name', function ($name) use ($app) {
        $method = ($app->request->post('f')) ? 'firstOrCreate' : 'create_new';
        $model = call_user_func(array(Model\App::collection($name), $method), $app->collection_data);

        if ($model->isModified() && !$model->save()) {
            throw new ForbiddenException("Can't save '{$model->getName()}'.");
        }

        $app->content = $model;
    });

    /**
     * PUT /collection/:name
     */
    $app->put('/:name', function ($name) use ($app) {
        $query = Model\App::collection($name)->filter($app->request->post('q'));

        if ($operation = $app->request->post('op')) {
            // Operations: increment/decrement
            $app->content = $query->{$operation['method']}($operation['field'], $operation['value']);
        } else {

            // Perform raw update
            $affected = $query->update($app->collection_data);
            $app->content = array(
                'success' => is_int($affected) && $affected > 0,
                'affected' => $affected
            );
        }
    });

    /**
     * TODO: DRY with PUT /collection/:name
     * PUT /collection/:name/:id
     * Curently only used on : Only Backbone.DLModel
     */
    $app->put('/:name/:id', function ($name, $id) use ($app) {
        $query = Model\App::collection($name)->where('_id', $id);

        if ($operation = $app->request->post('op')) {
            // Operations: increment/decrement
            $app->content = $query->{$operation['method']}($operation['field'], $operation['value']);
        } else {
            // Perform raw update
            $app->content = array('success' => $query->update($app->collection_data) === 1);
        }
    });

    /**
     * DELETE /collection/:name
     */
    $app->delete('/:name', function ($name) use ($app) {
        $query = Model\App::collection($name)->filter($app->request->post('q'));
        $app->content = array('success' => $query->delete());
    });

    /**
     * GET /collection/:name/:id
     */
    $app->get('/:name/:id', function ($name, $id) use ($app) {
        $app->content = Model\App::collection($name)->find($id);
    });

    /**
     * POST /collection/:name/:id
     */
    $app->post('/:name/:id', function ($name, $id) use ($app) {
        $collection = Model\App::collection($name);
        if ($model = $collection->find($id)) {
            if ($model->fill($app->collection_data) && $model->isModified()) {
                if (!$model->save()) {
                    throw new ForbiddenException("Can't save '{$collection->getName()}'.");
                }
            }
        }
        $app->content = $model;
    });

    /**
     * DELETE /collection/:name/:id
     */
    $app->delete('/:name/:id', function ($name, $id) use ($app) {
        $app->content = array('success' => Model\App::collection($name)->find($id)->delete());
    });

    /**
     * Nested collections
     */
    // $app->get('/:name/:id', function ($name, $id) use ($app) {
    // 	$app->content = array('success' => Model\Collection::query()->from($name)->where('_id', $id)->delete());
    // });

});

/**
 * Realtime channels
 */
$app->group('/channels', function () use ($app) {
    /**
     * GET /channels/channel
     * GET /channels/some/deep/channel
     */
    $app->get('/:name+', function ($name) use ($app) {
        $name = implode("/",$name);
        $app->content = Model\App::collection('channel_messages')->filter($app->request->get('q'))
            ->where('channel', $name)
            ->get();
    });

    /**
     * POST /channels/channel
     * POST /channels/some/deep/channel
     */
    $app->post('/:name+', function ($name) use ($app) {
        $name = implode("/",$name);
        $app->content = Model\ChannelMessage::create(array_merge($app->request->post(), array(
            'channel' => $name
        )));
    });
});

/**
 * Authentication API
 */
$app->group('/auth', function () use ($app) {
    /**
     * GET /auth
     */
    $app->get('/', function () use ($app) {
        $app->content = Model\Auth::current();
    });

    /**
     * POST /auth/facebook
     * POST /auth/email
     */
    $app->post('/:provider(/:method)', function ($provider_name, $method = 'register') use ($app) {
        $app->content = Auth\Provider::get($provider_name)->{$method}($app->collection_data);
    });
});

/**
 * Key/value routes
 */
$app->group('/key', function () use ($app) {

    /**
     * GET /key/:name
     */
    $app->get('/:key', function ($key) use ($app) {
        $app->content = Model\KeyValue::where('name', $key)->first() ?: new Model\KeyValue();
    });

    /**
     * PUT /key/:name
     */
    $app->post('/:key', function ($key) use ($app) {
        $app->content = Model\KeyValue::upsert(array(
            'name' => $key,
            'value' => $app->request->post('value')
        ));
    });

});

/**
 * File API
 */
$app->group('/files', function () use ($app) {

    /**
     * GET /files/:id
     */
    $app->get('/:id', function ($id) use ($app) {
        $file = Model\File::find($id);
        if (!$file) {
            $app->response->setStatus(404);

            return;
        }
        $app->content = $file;
    });

    /**
     * DELETE /files/:id
     */
    $app->delete('/:id', function ($id) use ($app) {
        $file = Model\File::find($id);
        if (!$file) {
            $app->response->setStatus(404);

            return;
        }
        $app->content = array('success' => ($file->delete() == 1));
    });

    /**
     * POST /files
     */
    $app->post('/', function () use ($app) {
        if (!isset($_FILES["file"])) {
            throw new \Exception("'file' field is required.");
        }

        $app->content = Model\File::create(array(
            'file' => $_FILES["file"]
        ));
    });

});

/**
 * Push Notifications / Installations
 */
$app->group('/push', function () use ($app) {
    /**
     * POST /push/registration
     */
    $app->post('/registration', function () use ($app) {
        $data = $app->request->post('d') ?: $app->request->post('data') ?: $app->request->post();
        $app->content = Model\PushRegistration::create($data);
    });

    /**
     * DELETE /push/registration
     */
    $app->delete('/registration', function () use ($app) {
        $data = $app->request->post('d') ?: $app->request->post('data') ?: $app->request->post();
        if (!isset($data['device_id'])) {
            throw new \Exception("'device_id' is required to delete push registration.");
        }
        $registration = Model\PushRegistration::where('device_id', $data['device_id']);
        $app->content = array('success' => ($registration->delete() == 1));
    });

    /**
     * GET /notify
     */
    $app->get('/notify', function () use ($app) {
        if (!$app->request->headers->get('X-Scheduled-Task')) {
            throw new \Exception("Oops.");
        }

        $notifier = new PushNotification\Notifier();
        $messages = Model\App::collection('push_messages')->where('status', Model\PushMessage::STATUS_QUEUE);
        $app->content = $notifier->push_messages($messages);
    });

});

/**
 * Internals
 */
$app->group('/apps', function () use ($app) {

    /**
     * Create a new app
     *
     * POST /apps/
     */
    $app->post('/', function () use ($app) {
        // Reset table prefix
        \DLModel::getConnectionResolver()->connection()->setTablePrefix('');

        $data = Model\App::create($app->request->post('app'));
        $response = $data->toArray();

        // Set application prefix for migration
        AppContext::setKey($data->keys[0]);
        AppContext::migrate();

        $app->content = $response;
    });

    /**
     * Clear application cache
     */
    $app->delete('/cache', function () use ($app) {
        Cache::flush();
        $app->content = array('success' => true);
    });

    $app->get('/logs', function () use ($app) {
        $file_path = $app->log->getWriter()->getFilePath();
        $is_tail = ($app->request->get('tail')) ? '-f ' : '';
        $lines = $app->request->get('n', 30);

        $handle = popen("tail -n {$lines} {$is_tail} {$file_path} 2>&1", 'r');
        $content = "";
        while (!feof($handle)) {
            $content .= fgets($handle);
            ob_flush();
            flush();
            usleep(300);
        }
        pclose($handle);
        $app->content = array('text' => $content);
    });

    /**
     * GET /apps/list
     */
    $app->get('/list', function () use ($app) {
        $app->content = Model\App::all();
    });

    /**
     * GET /apps/by_name/:name
     */
    $app->get('/by_name/:name', function ($name) use ($app) {
        $app->content = Model\App::where('name', $name)->first();
    });

    /**
     * Keys
     */
    $app->get('/keys', function () use ($app) {
        $app->content = $app->key->app->toArray();
    });

    $app->post('/keys', function () use ($app) {
        $app->content = $app->key->app->generate_key();
    });

    /**
     * Scheduled tasks
     */
    $app->post('/tasks', function () use ($app) {
        // Remove all scheduled tasks for this app
        Model\ScheduledTask::delete();

        $tasks = "";
        foreach ($app->request->post('schedule', array()) as $schedule) {
            $task = Model\ScheduledTask::create($schedule);
            $tasks .= $task->getCommand() . "\n";
        }
        file_put_contents(__DIR__ . '/app/storage/crontabs/' . $app->key->app_id . '.cron', $tasks);

        $app->content = array('success' => Model\ScheduledTask::install());
    });

    $app->get('/tasks', function () use ($app) {
        $app->content = Model\ScheduledTask::all()->toArray();
    });

    /**
     * GET /apps/deploy
     */
    $app->get('/deploy', function () use ($app) {
        $app->content = array('modules' => Model\Module::dump());
    });

    /**
     * POST /apps/deploy
     *
     * TODO: atomic deployment
     */
    $app->post('/deploy', function () use ($app) {
        // application configs
        Model\AppConfig::deploy($app->request->post('config', array()));

        // application secrets
        Model\AppConfig::deploy($app->request->post('security', array()), array('security'));

        // invalidate previous configurations
        Model\AppConfig::where('updated_at', '<', Carbon::now())->delete();

        $collections_migrated = 0;
        foreach($app->request->post('schema', array()) as $collection => $config) {
            if (Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config)) {
                $collections_migrated += 1;
            }
        }

        $app->content = array(
            // schema
            'schema' => $collections_migrated,

            // scheduled tasks
            'schedule' => Model\ScheduledTask::deploy($app->request->post('schedule', array())),

            // modules
            'modules' => Model\Module::deploy($app->request->post('modules', array()))
        );
    });

    /**
     * Configurations
     */
    $app->get('/configs', function () use ($app) {
        $app->content = Model\AppConfig::all();
    });

    $app->post('/configs', function () use ($app) {
        foreach ($app->request->post('configs', array()) as $config) {
            $_config = Model\AppConfig::firstOrNew(array('name' => $config['name']));

            // $existing = $_app->configs()->where('name', $config['name'])->first();
            // if ($existing) {
            //     $existing->update($config);
            // } else {
            //     $_app->configs()->create($config);
            // }
        }
        $app->content = array('success' => true);
    });

    /**
     * Modules
     */
    $app->get('/modules', function () use ($app) {
        $app->content = $app->key->app->modules;
    });

    $app->post('/modules', function () use ($app) {
        $data = $app->request->post('module');

        // try to retrieve existing module for this app
        $module = Model\Module::where('name', $data['name'])->first();

        $app->content = ($module) ? $module->update($data) : Model\Module::create($data);
    });

    /**
     * Schema
     */
    $app->post('/schema', function () use ($app) {
        $schema = $app->request->post();

        foreach($schema as $collection => $config) {
            Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config);
        }

        $app->content = array('success' => true);
    });

    $app->get('/schema', function () use ($app) {
        $app->content = Schema\Builder::dump();
    });

    // $app->get('/:name/composer', function ($id) use ($app) {
    // // $app->post('/:id/composer', function ($id) use ($app) {
    // 	// $composer = json_decode(file_get_contents('composer.json'));
    // 	// $composer['require'] = array_merge($composer['require'], $app->request->post('require'));
    // 	// file_put_contents('composer.json', json_encode($composer));
    // 	//Create the application and run it with the commands
    // 	error_reporting(E_ALL);
    // 	ini_set('display_errors', 1);
    // 	chdir('../');
    // 	$application = new Composer\Console\Application();
    // 	$application->run(new Symfony\Component\Console\Input\ArgvInput(array('update', 'willdurand/geocoder')));
    // 	var_dump("Finished.");
    // });

    $app->delete('/:id', function ($id) {
        echo json_encode(array('success' => $app->key->app->destroy()));
    });
});

$app->run();
