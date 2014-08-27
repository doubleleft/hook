<?php namespace Hook\Http;

use Hook\Middlewares;

class Router {
    protected static $instance;

    public static function setup($app)
    {
        static::setInstance($app);

        //
        // Setup middlewares
        //
        $app->add(new Middlewares\ResponseTypeMiddleware());
        $app->add(new Middlewares\LogMiddleware());
        $app->add(new Middlewares\AuthMiddleware());
        $app->add(new Middlewares\AppMiddleware());

        return static::registerCoreRoutes($app);
    }

    public static function registerCoreRoutes($app)
    {
        // System
        $app->get('/system/time', 'Hook\\Controllers\\SystemController:time');
        $app->get('/system/ip', 'Hook\\Controllers\\SystemController:ip');

        // Collections
        $app->get('/collection/:name', 'Hook\\Controllers\\CollectionController:index');
        $app->post('/collection/:name', 'Hook\\Controllers\\CollectionController:store');
        $app->put('/collection/:name', 'Hook\\Controllers\\CollectionController:put');
        $app->put('/collection/:name/:id', 'Hook\\Controllers\\CollectionController:put');
        $app->post('/collection/:name/:id', 'Hook\\Controllers\\CollectionController:post');
        $app->delete('/collection/:name(/:id)', 'Hook\\Controllers\\CollectionController:delete');

        // Auth
        $app->get('/auth', 'Hook\\Controllers\\AuthController:show');
        $app->post('/auth/:provider(/:method)', 'Hook\\Controllers\\AuthController:execute');

        // Key/Value
        $app->get('/key/:name', 'Hook\\Controllers\\KeyValueController:show');
        $app->post('/key/:name', 'Hook\\Controllers\\KeyValueController:store');
        $app->delete('/key/:name', 'Hook\\Controllers\\KeyValueController:delete');

        // Channels
        $app->get('/channel/:name+', 'Hook\\Controllers\\ChannelController:index');
        $app->post('/channel/:name+', 'Hook\\Controllers\\ChannelController:store');

        // Push Notifications
        $app->post('/push/registration', 'Hook\\Controllers\\PushNotificationController:store');
        $app->delete('/push', 'Hook\\Controllers\\PushNotificationController:delete');
        $app->get('/push/notify', 'Hook\\Controllers\\PushNotificationController:notify');

        // App management
        $app->get('/apps', 'Hook\\Controllers\\AppsController:index');
        $app->post('/apps', 'Hook\\Controllers\\AppsController:create');
        $app->delete('/apps', 'Hook\\Controllers\\AppsController:delete');
        $app->delete('/apps/cache', 'Hook\\Controllers\\AppsController:delete_cache');
        $app->get('/apps/logs', 'Hook\\Controllers\\AppsController:logs');
        $app->get('/apps/tasks', 'Hook\\Controllers\\AppsController:tasks');
        $app->post('/apps/tasks', 'Hook\\Controllers\\AppsController:recreate_tasks');
        $app->get('/apps/deploy', 'Hook\\Controllers\\AppsController:dump_deploy');
        $app->post('/apps/deploy', 'Hook\\Controllers\\AppsController:deploy');
        $app->get('/apps/configs', 'Hook\\Controllers\\AppsController:configs');
        $app->get('/apps/modules', 'Hook\\Controllers\\AppsController:modules');
        $app->get('/apps/schema', 'Hook\\Controllers\\AppsController:schema');
        $app->post('/apps/schema', 'Hook\\Controllers\\AppsController:upload_schema');

        $app->notFound(function () use ($app) {
            echo json_encode(array('error' => 'not_found'));
        });

        //
        // Output exceptions as JSON {'error':'message'}
        //
        $app->error(function($e) use ($app) {
            echo json_encode(array('error' => $e->getMessage()));
        });

        return $app;
    }

    public static function mount($path, $controller_klass)
    {
        $methods = get_class_methods($controller_klass);

        // skip
        if (!$methods) {
            debug("'{$controller_klass}' has no methods.");
            return;
        }

        foreach($methods as $method_name) {
            // skip invalid methods
            if ($method_name == '__construct') {
                continue;
            }

            // call 'mounted' method
            if ($method_name == 'mounted') {
                call_user_func(array($controller_klass, 'mounted'), $path);
                continue;
            }

            preg_match_all('/^(get|put|post|patch)(.*)/', $method_name, $matches);
            $has_matches = (count($matches[1]) > 0);

            $http_method = $has_matches ? $matches[1][0] : 'any';
            $route_name = $has_matches ? $matches[2][0] : $method_name;

            $route = str_finish($path, '/');
            if ($route_name !== 'index') {
                $route .= snake_case($route_name);
            }

            static::$instance->{$http_method}($route, "{$controller_klass}:{$method_name}");
        }
    }

    public static function setInstance($instance) {
        static::$instance = $instance;
    }

    public static function getInstance() {
        return static::$instance;
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(static::$instance, $method), $arguments);
    }

}
