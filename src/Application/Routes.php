<?php namespace Hook\Application;

use Hook\Middlewares;
use Hook\Http\Router;

class Routes {

    /**
     * Add Hook Middlewares to Slim instance.
     * @param \Slim\Slim
     * @return \Slim\Slim;
     */
    public static function mounted($path)
    {
        $app = Router::getInstance();

        // Setup middlewares
        $app->add(new Middlewares\ResponseTypeMiddleware());
        $app->add(new Middlewares\ChannelMiddleware());
        $app->add(new Middlewares\LogMiddleware());
        $app->add(new Middlewares\AuthMiddleware());
        // $app->add(new Middlewares\SessionMiddleware());
        $app->add(new Middlewares\AppMiddleware());
        $app->add(new Middlewares\MethodOverride());

        // System
        $app->get($path . 'system/time', 'Hook\\Controllers\\SystemController:time');
        $app->get($path . 'system/ip', 'Hook\\Controllers\\SystemController:ip');

        // Collections
        $app->get($path . 'collection/:name', 'Hook\\Controllers\\CollectionController:index');
        $app->post($path . 'collection/:name', 'Hook\\Controllers\\CollectionController:store');
        $app->get($path . 'collection/:name/:id', 'Hook\\Controllers\\CollectionController:show');
        $app->put($path . 'collection/:name', 'Hook\\Controllers\\CollectionController:put');
        $app->put($path . 'collection/:name/:id', 'Hook\\Controllers\\CollectionController:put');
        $app->post($path . 'collection/:name/:id', 'Hook\\Controllers\\CollectionController:post');
        $app->delete($path . 'collection/:name(/:id)', 'Hook\\Controllers\\CollectionController:delete');

        // Auth
        $app->get($path . 'auth', 'Hook\\Controllers\\AuthController:show');
        $app->post($path . 'auth/email', 'Hook\\Controllers\\AuthController:register');
        $app->post($path . 'auth/email/login', 'Hook\\Controllers\\AuthController:login');
        $app->post($path . 'auth/email/forgotPassword', 'Hook\\Controllers\\AuthController:forgotPassword');
        $app->post($path . 'auth/email/resetPassword', 'Hook\\Controllers\\AuthController:resetPassword');
        $app->post($path . 'auth/update', 'Hook\\Controllers\\AuthController:update');

        // OAuth
        $app->get($path . 'oauth/relay_frame', 'Hook\\Controllers\\OAuthController:relay_frame');
        $app->get($path . 'oauth/:strategy(/:callback)', 'Hook\\Controllers\\OAuthController:auth');
        $app->post($path . 'oauth/callback', 'Hook\\Controllers\\OAuthController:auth');

        // Key/Value
        $app->get($path . 'key/:name', 'Hook\\Controllers\\KeyValueController:show');
        $app->post($path . 'key/:name', 'Hook\\Controllers\\KeyValueController:store');
        $app->delete($path . 'key/:name', 'Hook\\Controllers\\KeyValueController:delete');

        // Channels
        $app->get($path . 'channel/:name+', 'Hook\\Controllers\\ChannelController:index');
        $app->post($path . 'channel/:name+', 'Hook\\Controllers\\ChannelController:store');

        // Push Notifications
        $app->post($path . 'push/registration', 'Hook\\Controllers\\PushNotificationController:store');
        $app->delete($path . 'push', 'Hook\\Controllers\\PushNotificationController:delete');
        $app->get($path . 'push/notify', 'Hook\\Controllers\\PushNotificationController:notify');

        // Application management
        $app->get($path . 'apps', 'Hook\\Controllers\\ApplicationController:index');
        $app->post($path . 'apps', 'Hook\\Controllers\\ApplicationController:create');
        $app->delete($path . 'apps', 'Hook\\Controllers\\ApplicationController:delete');
        $app->delete($path . 'apps/cache', 'Hook\\Controllers\\ApplicationController:delete_cache');
        $app->get($path . 'apps/logs', 'Hook\\Controllers\\ApplicationController:logs');
        $app->get($path . 'apps/tasks', 'Hook\\Controllers\\ApplicationController:tasks');
        $app->post($path . 'apps/tasks', 'Hook\\Controllers\\ApplicationController:recreate_tasks');
        $app->get($path . 'apps/deploy', 'Hook\\Controllers\\ApplicationController:dump_deploy');
        $app->post($path . 'apps/deploy', 'Hook\\Controllers\\ApplicationController:deploy');
        $app->get($path . 'apps/configs', 'Hook\\Controllers\\ApplicationController:configs');
        $app->get($path . 'apps/keys', 'Hook\\Controllers\\ApplicationController:keys');
        $app->get($path . 'apps/modules', 'Hook\\Controllers\\ApplicationController:modules');
        $app->get($path . 'apps/schema', 'Hook\\Controllers\\ApplicationController:schema');
        $app->post($path . 'apps/schema', 'Hook\\Controllers\\ApplicationController:upload_schema');
        $app->post($path . 'apps/evaluate', 'Hook\\Controllers\\ApplicationController:evaluate');

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

}
