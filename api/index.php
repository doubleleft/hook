<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

require __DIR__ . '/vendor/autoload.php';
use Composer\Command\UpdateCommand;

$app = new \Slim\Slim(array(
	// 'cookies.encrypt' => true,
	// 'cookies.lifetime' => '8 hours',
	// 'cookies.path' => '/',
));
require __DIR__ . '/app/bootstrap.php';

// Middlewares
$app->add(new LogMiddleware());
$app->add(new ResponseTypeMiddleware());
$app->add(new AppAuthMiddleware());

$app->get('/', function() use ($app) {
	$app->content = models\App::all();
});

/**
 * Collection routes
 */
$app->group('/collection', function () use ($app) {

	/**
	 * GET /collection/:name
	 */
	$app->get('/:name', function($name) use ($app) {
		$query = models\Collection::query()->from($name);
		$query->where('app_id', $app->key->app_id);

		// Apply filters
		if ($q = $app->request->get('q')) {
			foreach($q as $where) {
				$query->where($where[0], $where[1], $where[2]);
			}
		}

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
			// Aggregate count/max/min/avg/sum methods
			$app->content = $query->{$aggr['method']}($aggr['field']);

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
		$module = models\Module::where('app_id', $app->key->app_id)->
			where('type', 'observers')->
			where('name', "{$name}.php")->
			first();

		if ($module) {
			eval(substr($module->code, 5));
			$klass = ucfirst($name);
			if (class_exists($klass)) {
				models\Collection::observe(new $klass);
			} else {
				throw new Exception("Module '{$name}.php' must define a class named '{$klass}'.");
			}
		}

		$model = new models\Collection(array_merge($app->request->post('data'), array(
			'app_id' => $app->key->app_id,
			'table_name' => $name
		)));

		if (!$model->save()) {
			throw new ErrorException("Can't save '{$name}'.");
		}

		$app->content = $model;
	});

	/**
	 * PUT /collection/:name
	 */
	$app->put('/:name', function($name) use ($app) {
		$query = models\Collection::query()->from($name);
		$query->where('app_id', $app->key->app_id);

		// Apply filters
		if ($q = $app->request->get('q')) {
			foreach($q as $where) {
				$query->where($where[0], $where[1], $where[2]);
			}
		}

		$query->update();

		$app->content = models\Collection::create(array_merge($app->request->post('data'), array(
			'app_id' => $app->key->app_id,
			'table_name' => $name
		)));
	});

	/**
	 * DELETE /collection/:name
	 */
	$app->delete('/:name', function($name) use ($app) {
		$coll = new models\Collection(array('table_name' => $name));
		$app->content = array('success' => $coll->drop());
	});

	/**
	 * GET /collection/:name/:id
	 */
	$app->get('/:name/:id', function($name, $id) use ($app) {
		$app->content = models\Collection::query()
			->from($name)
			->find($id);
	});

	/**
	 * POST /collection/:name/:id
	 */
	$app->post('/:name/:id', function($name, $id) use ($app) {
		$app->content = array(
			'success' => (models\Collection::query()
				->from($name)
				->where('_id', '=', $id)
				->update($app->request->post('data')) === 1)
		);
	});

	/**
	 * DELETE /collection/:name/:id
	 */
	$app->delete('/:name/:id', function($name, $id) use ($app) {
		$app->content = array('success' => models\Collection::query()->from($name)->where('_id', $id)->delete());
	});

	/**
	 * Nested collections
	 */
	// $app->get('/:name/:id', function($name, $id) use ($app) {
	// 	$app->content = array('success' => models\Collection::query()->from($name)->where('_id', $id)->delete());
	// });


});

/**
 * Authentication
 */
$app->group('/auth', function() use ($app) {
	$app->post('/:provider', function($provider_name) use ($app) {
		$userdata = Auth\Provider::get($provider_name)->register($app->request->post());
		// $userdata['token']['token']
		$app->content = $userdata;
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
 * File routes
 */
$app->group('/files', function() use($app) {

	/**
	 * GET /files/:id
	 */
	$app->get('/:id', function($id) {
		return File::find($id)->toJson();
	});

	/**
	 * POST /files/:id
	 */
	$app->get('/', function($id) use ($app) {
		$app->content = File::create(array(
			'app_id' => $app->key->app_id,
			'file' => $app->request->file('file')
		));
	});

});

// // internals
$app->group('/apps', function() use ($app) {
	$app->get('/test', function() use ($app) {
		models\App::truncate();

		if (models\App::count() == 0) {
			$app = models\App::create(array(
				'_id' => 1,
				'name' => "test"
			));
			$app->keys()->create(array(
				'key' => 'test',
				'secret' => 'test'
			));
		}

		$app->content = models\App::all();
	});

	$app->get('/', function() use($app) {
		$app->content = models\App::all();
	});

	$app->post('/', function() use ($app) {
		$app->content = models\App::create($app->request->post('app'));
	});

	$app->get('/:name', function($id) {
		$app->content = models\App::find($id);
	});

	$app->get('/:name/configs', function($name) use ($app) {
		$_app = models\App::where('name', $name)->first();
		$app->content = $_app->configs;
	});

	$app->post('/:name/configs', function($name) use ($app) {
		$_app = models\App::where('name', $name)->first();
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

	$app->delete('/:name/configs/:config', function($name, $config) use ($app) {
		$_app = models\App::where('name', $name)->first();
		$config = models\AppConfig::where('app_id', $_app->_id)->
			where('name', $config)->
			first();
		$app->content = array('success' => $config->delete());
	});

	$app->get('/:name/modules', function($name) use ($app) {
		$_app = models\App::where('name', $name)->first();
		if (!$_app) {
			$app->content = array('error' => 'App not found.');
		} else {
			$app->content = $_app->modules;
		}
	});

	$app->post('/:name/modules', function($name) use ($app) {
		$data = $app->request->post('module');

		$_app = models\App::where('name', $name)->first();
		$data['app_id'] = $_app->_id;

		// try to retrieve existing module for this app
		$module = models\Module::where('app_id', $data['app_id'])
			->where('name', $data['name'])
			->first();

		$app->content = ($module) ? $module->update($data) : models\Module::create($data);
	});

	$app->delete('/:name/modules/:module', function($name, $module_name) use ($app) {
		$_app = models\App::where('name', $name)->first();
		$deleted = models\Module::where('app_id', $_app->_id)->
			where('name', $module_name)->
			delete();
		$app->content = array('success' => $deleted);
	});

	$app->put('/:name', function($id) use ($app) {
		$app->content = models\App::find($id)->update($app->request->post('data'));
	});

	$app->get('/:name/composer', function($id) use ($app) {
	// $app->post('/:id/composer', function($id) use ($app) {
		// $composer = json_decode(file_get_contents('composer.json'));
		// $composer['require'] = array_merge($composer['require'], $app->request->post('require'));
		// file_put_contents('composer.json', json_encode($composer));
		//Create the application and run it with the commands
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		chdir('../');
		$application = new Composer\Console\Application();
		$application->run(new Symfony\Component\Console\Input\ArgvInput(array('update', 'willdurand/geocoder')));
		var_dump("Finished.");
	});

	$app->delete('/:name', function($id) {
		echo json_encode(array('success' => models\App::query()->delete($id)));
	});
});

$app->run();
