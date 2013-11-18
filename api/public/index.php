<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
require '../app/bootstrap.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-type', 'application/json');

$app->get('/', function() {
	Models\App::all()->each(function($model) {
		if ($model->name != "test") {
			$model->delete();
		}
	});

	if (Models\App::count() == 0) {
		Models\App::create(array('name' => "test"));
	}

	echo Models\App::all()->toJson();
});


/**
 * Collection routes
 */
$app->group('/collection', function () use ($app) {
	$app->add(new AuthMiddleware());

	/**
	 * GET /collection/:name
	 */
	$app->get('/:name', function($name) use ($app) {
		echo Models\Collection::where('app_id', $app->key->app_id)
			->where('c', $name)
			->get()
			->toJson();
	});

	/**
	 * GET /collection/:name/:id
	 */
	$app->get('/:name/:id', function($name, $id) use ($app) {
		echo Models\Collection::where('c', $name)
			->where('_id', $id)
			->get()
			->toJson();
	});

	/**
	 * POST /collection/:name
	 */
	$app->post('/:name', function($name) use ($app) {
		$data = array_merge($app->request->post('data'), array(
			'app_id' => $app->key->app_id,
			'c' => $name
		));
		echo Models\Collection::create($data)->toJson();
	});

	/**
	 * DELETE /collection/:name/:id
	 */
	$app->delete('/:name/:id', function($name) use ($app) {
		echo json_encode(array(
			'success' => Models\Collection::where('_', $name)->delete($id)
		));
	});

});

// // internals
// $app->group('/apps', function() use ($app) {
// 	// $app->add(new AuthMiddleware(/* administrator? */));
// 	$app->get('/', function() {});
// 	$app->post('/', function() {});
// 	$app->put('/:id', function($id) {});
// });

$app->run();
