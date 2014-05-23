<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

$app = require __DIR__ . '/app/bootstrap.php';

// Middlewares
$app->add(new LogMiddleware());
$app->add(new ResponseTypeMiddleware());
$app->add(new AppMiddleware());

// Attach user authentication
$app->add(new AuthMiddleware());

$app->get('/', function() use ($app) {
	$app->content = models\App::all();
});

/**
 * Misc system utilities
 */
$app->group('/system', function() use ($app) {
	/**
	 * GET /system/time
	 */
	$app->get('/time', function() use ($app) {
		$app->content = time();
	});
    /**
     * GET /system/ip
     */
    $app->get('/ip', function() use ($app) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if(isset($_SERVER["HTTP_X_REAL_IP"])){
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
		foreach($data as $key => $value){
			if (models\File::base64($value)){
				$attached_files[$key] = $value;
			}
		}

		if (!empty($_FILES)) {
			$attached_files = array_merge($attached_files, $_FILES);
		}

		if(!empty($attached_files)) {
			$data[models\Collection::ATTACHED_FILES] = $attached_files;
		}
		return $data;
	});

	/**
	 * GET /collection/:name
	 */
	$app->get('/:name', function($name) use ($app) {
		$query = models\App::collection($name)->filter($app->request->get('q'));

		// Apply ordering
		if ($s = $app->request->get('s')) {
			foreach($s as $ordering) {
				$query->orderBy(reset($ordering), end($ordering));
			}
		}

		// Apply group
		if ($group = $app->request->get('g')) {
			foreach($group as $field) {
				$query = $query->groupBy($field);
			}
		}

		// limit / offset
		if ($offset = $app->request->get('offset')) {
			$query = $query->skip($offset);
		}
		if ($limit = $app->request->get('limit')) {
			$query = $query->take($limit);
		}

		if ($aggr = $app->request->get('aggr')) {
			// Aggregate 'max'/'min'/'avg'/'sum' methods
			if ($aggr['field']) {
				$app->content = $query->{$aggr['method']}($aggr['field']);
			} else {
				// Aggregate 'count'
				$app->content = $query->{$aggr['method']}();
			}

		} else if ($app->request->get('p')) {
			// Apply pagination
			$app->content = $query->paginate($app->request->get('p'));

		} else if ($app->request->get('f')) {
			// First
			$app->content = $query->first();

		} else {
			$app->content = $query->get();
		}

	});

	/**
	 * POST /collection/:name
	 */
	$app->post('/:name', function($name) use ($app) {
		$model = models\App::collection($name)->create_new($app->collection_data);

		if (!$model->save()) {
			throw new ForbiddenException("Can't save '{$name}'.");
		}

		$app->content = $model;
	});

	/**
	 * PUT /collection/:name
	 */
	$app->put('/:name', function($name) use ($app) {
		$query = models\App::collection($name)->filter($app->request->post('q'));

		if ($operation = $app->request->post('op')) {
			// Operations: increment/decrement
			$app->content = $query->{$operation['method']}($operation['field'], $operation['value']);
		} else {

			// Perform raw update
			//
			// FIXME: 'd' is deprecated. use 'data' instead.
			//
			// Who is using it?
			// - 'plugados-site'
			// - 'clubsocial-possibilidades'
			//
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
	$app->put('/:name/:id', function($name, $id) use ($app) {
		$query = models\App::collection($name)->where('_id', $id);

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
	$app->delete('/:name', function($name) use ($app) {
		$query = \models\App::collection($name)->filter($app->request->post('q'));
		$app->content = array('success' => $query->delete());
	});

	/**
	 * GET /collection/:name/:id
	 */
	$app->get('/:name/:id', function($name, $id) use ($app) {
		$app->content = models\App::collection($name)->find($id);
	});

	/**
	 * POST /collection/:name/:id
	 */
	$app->post('/:name/:id', function($name, $id) use ($app) {
		$model = models\App::collection($name)->find($id);
		if (!$model->update($app->collection_data)) {
			throw new ForbiddenException("Can't save '{$name}'.");
		}
		$app->content = $model;
	});

	/**
	 * DELETE /collection/:name/:id
	 */
	$app->delete('/:name/:id', function($name, $id) use ($app) {
		$app->content = array('success' => models\App::collection($name)->find($id)->delete());
	});

	/**
	 * Nested collections
	 */
	// $app->get('/:name/:id', function($name, $id) use ($app) {
	// 	$app->content = array('success' => models\Collection::query()->from($name)->where('_id', $id)->delete());
	// });

});

/**
 * Realtime channels
 */
$app->group('/channels', function() use ($app) {
	/**
	 * GET /channels/channel
	 * GET /channels/some/deep/channel
	 */
	$app->get('/:name+', function($name) use ($app) {
		$name = implode("/",$name);
		$app->content = models\App::collection('channel_messages')->filter($app->request->get('q'))
			->where('channel', $name)
			->get();
	});

	/**
	 * POST /channels/channel
	 * POST /channels/some/deep/channel
	 */
	$app->post('/:name+', function($name) use ($app) {
		$name = implode("/",$name);
		$app->content = models\ChannelMessage::create(array_merge($app->request->post(), array(
			'app_id' => $app->key->app_id,
			'channel' => $name
		)));
	});
});

/**
 * Authentication API
 */
$app->group('/auth', function() use ($app) {
	/**
	 * GET /auth
	 */
	$app->get('/', function() use ($app) {
		$app->content = models\Auth::current();
	});

	/**
	 * POST /auth/facebook
	 * POST /auth/email
	 */
	$app->post('/:provider(/:method)', function($provider_name, $method = 'authenticate') use ($app) {
		$data = $app->collection_data;
		$data['app_id'] = $app->key->app_id;
		$app->content = Auth\Provider::get($provider_name)->{$method}($data);
	});
});


/**
 * Key/value routes
 */
$app->group('/key', function() use ($app) {

	/**
	 * GET /key/:name
	 */
	$app->get('/:key', function($key) use ($app) {
		$app->content = models\KeyValue::where('app_id', $app->key->app_id)
			->where('name', $key)
			->first() ?: new models\KeyValue();
	});

	/**
	 * PUT /key/:name
	 */
	$app->post('/:key', function($key) use ($app) {
		$app->content = models\KeyValue::upsert(array(
			'app_id' => $app->key->app_id,
			'name' => $key,
			'value' => $app->request->post('value')
		));
	});

});

/**
 * File API
 */
$app->group('/files', function() use($app) {

	/**
	 * GET /files/:id
	 */
	$app->get('/:id', function($id) use ($app){
		$file = models\File::find($id);
		if (!$file) {
			$app->response->setStatus(404);
			return;
		}
		$app->content = $file;
	});

	/**
	 * DELETE /files/:id
	 */
	$app->delete('/:id', function($id) use ($app){
		$file = models\File::find($id);
		if (!$file) {
			$app->response->setStatus(404);
			return;
		}
		$app->content = array('success' => ($file->delete() == 1));
	});

	/**
	 * POST /files
	 */
	$app->post('/', function() use ($app) {
		if(!isset($_FILES["file"])){
			throw new \Exception("'file' field is required.");
		}

		$provider = AppConfig::get('storage.provider', 'filesystem');
		$raw_file = $_FILES["file"];
		$public_path = Storage\Provider::get($provider)->upload($raw_file);

		if($public_path == NULL){
			throw new \Exception("error when uploading file");
		}

		$file = models\File::create(array(
			'app_id' => $app->key->app_id,
			'file' => $raw_file,
			'path' => $public_path
		));

		$file->url = storageURL($file->path);
		$app->content = $file->toJson();
	});

});

/**
 * Push Notifications / Installations
 */
$app->group('/push', function() use ($app) {
	/**
	 * POST /push/registration
	 */
	$app->post('/registration', function() use ($app) {
		$data = $app->request->post('d') ?: $app->request->post('data') ?: $app->request->post();
		return models\PushRegistration::create(array_merge($data, array('app_id' => $app->key->app_id)));
	});

	/**
	 * DELETE /push/registration
	 */
	$app->delete('/registration', function() use ($app) {
		$data = $app->request->post('d') ?: $app->request->post('data') ?: $app->request->post();
		return models\PushRegistration::create(array_merge($data, array('app_id' => $app->key->app_id)));
	});

	/**
	 * GET /notify
	 */
	$app->get('/notify', function() use ($app) {
		if (!$app->request->headers->get('X-Scheduled-Task')) {
			throw new Exception("Oops.");
		}

		$notifier = new PushNotification\Notifier();
		$messages = models\App::collection('push_messages')->where('complete', false);
		$app->content = $notifier->push_messages($messages);
	});

});

/**
 * Internals
 */
$app->group('/apps', function() use ($app) {

	$app->get('/logs', function() use ($app) {
		$file_path = $app->log->getWriter()->getFilePath();
		$is_tail = ($app->request->get('tail')) ? '-f ' : '';
		$lines = $app->request->get('n', 30);

		$handle = popen("tail -n {$lines} {$is_tail} {$file_path} 2>&1", 'r');
		$content = "";
		while(!feof($handle)) {
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
	$app->get('/list', function() use($app) {
		$app->content = models\App::all();
	});

	/**
	 * GET /apps/by_name/:name
	 */
	$app->get('/by_name/:name', function($name) use ($app) {
		$app->content = models\App::where('name', $name)->first();
	});

	$app->post('/', function() use ($app) {
		$app->content = models\App::create($app->request->post('app'));
	});

	/**
	 * Keys
	 */
	$app->get('/keys', function() use ($app) {
		$app->content = $app->key->app->toArray();
	});

	$app->post('/keys', function() use ($app) {
		$app->content = $app->key->app->generate_key();
	});

	/**
	 * Scheduled tasks
	 */
	$app->post('/tasks', function() use ($app) {
		// Remove all scheduled tasks for this app
		models\ScheduledTask::current()->delete();

		$tasks = "";
		foreach($app->request->post('schedule', array()) as $schedule) {
			$task = models\ScheduledTask::create(array_merge($schedule, array('app_id' => $app->key->app_id)));
			$tasks .= $task->getCommand() . "\n";
		}
		file_put_contents(__DIR__ . '/app/storage/crontabs/' . $app->key->app_id . '.cron', $tasks);

		$app->content = array('success' => models\ScheduledTask::install());
	});

	$app->get('/tasks', function() use ($app) {
		$app->content = models\ScheduledTask::current()->get()->toArray();
	});

	/**
	 * Configurations
	 */
	$app->get('/configs', function() use ($app) {
		$app->content = $app->key->app->configs;
	});

	$app->post('/configs', function() use ($app) {
		$_app = $app->key->app;
		foreach($app->request->post('configs', array()) as $config) {
			$existing = $_app->configs()->where('name', $config['name'])->first();
			if ($existing) {
				$existing->update($config);
			} else {
				$_app->configs()->create($config);
			}
		}
		$app->content = array('success' => true);
	});

	$app->delete('/configs/:config', function($config) use ($app) {
		$app->content = array(
			'success' => ($app->key->app->configs()
				->where('name', $config)
				->first()
				->delete()) == 1
		);
	});

	/**
	 * Modules
	 */
	$app->get('/modules', function() use ($app) {
		$app->content = $app->key->app->modules;
	});

	$app->post('/modules', function() use ($app) {
		$data = $app->request->post('module');
		$data['app_id'] = $app->key->app_id;

		// try to retrieve existing module for this app
		$module = models\Module::where('app_id', $data['app_id'])
			->where('name', $data['name'])
			->first();

		$app->content = ($module) ? $module->update($data) : models\Module::create($data);
	});

	$app->delete('/modules', function($name) use ($app) {
		$data = $app->request->post('module');
		$deleted = models\Module::where('app_id', $app->key->app_id)->
			where('name', $data['name'])->
			delete();
		$app->content = array('success' => $deleted);
	});

	// $app->get('/:name/composer', function($id) use ($app) {
	// // $app->post('/:id/composer', function($id) use ($app) {
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

	$app->delete('/:id', function($id) {
		echo json_encode(array('success' => $app->key->app->destroy()));
	});
});

$app->run();
