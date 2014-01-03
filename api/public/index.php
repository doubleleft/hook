<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');
require '../vendor/autoload.php';

use Composer\Command\UpdateCommand;

$app = new \Slim\Slim();
require '../app/bootstrap.php';

// middlewares
$app->add(new LogMiddleware());
$app->add(new AuthMiddleware());

$app->response->headers->set('Content-type', 'application/json');

$app->get('/', function() {
	echo Models\App::all()->toJson();
});

/**
 * Collection routes
 */
$app->group('/collection', function () use ($app) {

	/**
	 * GET /collection/:name
	 */
	$app->get('/:name', function($name) use ($app) {
		$query = Models\Collection::query()->from($name);
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
		$result = ($app->request->get('p')) ? $query->paginate($app->request->get('p')) : $query->get();
		echo $result->toJson();
	});

	/**
	 * GET /collection/:name/:id
	 */
	$app->get('/:name/:id', function($name, $id) use ($app) {
		echo Models\Collection::query()
			->from($name)
			->find($id)
			->toJson();
	});

	/**
	 * POST /collection/:name
	 */
	$app->post('/:name', function($name) use ($app) {
		echo Models\Collection::create(array_merge($app->request->post('data'), array(
			'app_id' => $app->key->app_id,
			'table_name' => $name
		)))->toJson();
	});

	/**
	 * DELETE /collection/:name/:id
	 */
	$app->delete('/:name/:id', function($name) use ($app) {
		echo json_encode(array(
			'success' => Models\Collection::query()->from($name)->delete($id)
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
		return File::create(array(
			'app_id' => $app->key->app_id,
			'file' => $app->request->file('file')
		))->toJson();
	});

});

// // internals
$app->group('/apps', function() use ($app) {
	$app->get('/test', function() {
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
		echo Models\App::all()->toJson();
	});
	$app->get('/', function() {
		echo Models\App::all()->get()->toJson();
	});
	$app->post('/', function() use ($app) {
		echo Models\App::create($app->request->post('data'))->toJson();
	});
	$app->get('/:id', function($id) {
		echo Models\App::find($id)->toJson();
	});
	$app->get('/:id/modules', function() {
		echo Models\App::find($id)->modules->toJson();
	});
	$app->put('/:id', function($id) use ($app) {
		echo Models\App::find($id)->update($app->request->post('data'))->toJson();
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
