<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');
require '../vendor/autoload.php';

use Composer\Command\UpdateCommand;

$app = new \Slim\Slim();
require '../app/bootstrap.php';

// Middlewares
// Add EventSource middeware: http://en.wikipedia.org/wiki/Server-sent_events | http://www.html5rocks.com/en/tutorials/eventsource/basics/
$app->add(new ResponseTypeMiddleware());
$app->add(new LogMiddleware());
$app->add(new AuthMiddleware());

$app->get('/', function() use ($app) {
	$app->content =  Models\App::all();
});

/**
 * Collection routes
 */
$app->group('/collection', function () use ($app) {

	/**
	 * GET /collection/:name
	 */
	$app->get('/:name', function($name) use ($app) {
		$query = Models\Collection::query()->from(trim($name));
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

		// Apply pagination
		$app->content = ($app->request->get('p')) ? $query->paginate($app->request->get('p')) : $query->get();
	});

	/**
	 * GET /collection/:name/:id
	 */
	$app->get('/:name/:id', function($name, $id) use ($app) {
		$app->content = Models\Collection::query()
			->from($name)
			->find($id);
	});

	/**
	 * POST /collection/:name
	 */
	$app->post('/:name', function($name) use ($app) {
		$app->content = Models\Collection::create(array_merge($app->request->post('data'), array(
			'app_id' => $app->key->app_id,
			'table_name' => $name
		)));
	});

	/**
	 * DELETE /collection/:name/:id
	 */
	$app->delete('/:name/:id', function($name) use ($app) {
		echo json_encode(array(
			'success' => Models\Collection::query()->from($name)->delete($id)
		));
	});

	/**
	 * DELETE /collection/:name
	 */
	$app->delete('/:name', function($name) use ($app) {
		$coll = new Models\Collection(array('table_name' => $name));
		$app->content = array('success' => $coll->drop());
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
		$app->content = Models\KeyValue::where('app_id', $app->key->app_id)
			->where('name', $key)
			->first() ?: new Models\KeyValue();
	});

	/**
	 * PUT /key/:name
	 */
	$app->post('/:key', function($key) use ($app) {
		$app->content = Models\KeyValue::upsert(array(
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
		Models\App::truncate();

		if (Models\App::count() == 0) {
			$app = Models\App::create(array(
				'_id' => 1,
				'name' => "test"
			));
			$app->keys()->create(array(
				'key' => 'test',
				'secret' => 'test'
			));
		}

		$app->content = Models\App::all();
	});
	$app->get('/', function() use($app) {
		$app->content = Models\App::all();
	});
	$app->post('/', function() use ($app) {
		$app->content = Models\App::create($app->request->post('data'));
	});
	$app->get('/:id', function($id) {
		$app->content = Models\App::find($id);
	});
	$app->get('/:id/modules', function() use ($app) {
		$app->content = Models\App::find($id)->modules;
	});
	$app->put('/:id', function($id) use ($app) {
		$app->content = Models\App::find($id)->update($app->request->post('data'));
	});
	$app->get('/:id/composer', function($id) use ($app) {
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
	$app->delete('/:id', function($id) {
		echo json_encode(array('success' => Models\App::query()->delete($id)));
	});
});

$app->run();
