<?php
namespace Hook\Middlewares;

use Slim;
use Hook\Model\AppKey as AppKey;
use Hook\Model\Module as Module;
use Hook\Model\AppConfig as AppConfig;

use Hook\Package;
use Hook\Database\AppContext as AppContext;
use Hook\Exceptions\NotAllowedException as NotAllowedException;

class AppMiddleware extends Slim\Middleware
{

    public static function decode_query_string()
    {
        $app = Slim\Slim::getInstance();

        // Parse incoming JSON QUERY_STRING
        // OBS: that's pretty much an uggly thing, but we need data types here.
        // Every param is string on query string (srsly?)
        $query_string = $app->environment->offsetGet('QUERY_STRING');
        $query_data = array();

        if (strlen($query_string)>0) {
            $query_data = array();
            parse_str($query_string, $query_params);

            // Remove json data from query params (which just have key as param)
            $last_param = end($query_params);
            if (is_array($last_param) && end($last_param) === "") {
                array_pop($query_params);
            }

            // Decode JSON data from query params
            if (preg_match('/({[^$]+)/', urldecode($query_string), $query)) {
                $query_data = json_decode(urldecode($query[1]), true) ?: array();
            }

            // Parse remaining regular string variables
            $query_data = array_merge($query_data, $query_params);

            $app->environment->offsetSet('slim.request.query_hash', $query_data);
        }

        return $query_data;
    }

    public function call()
    {
        // The Slim application
        $app = $this->app;

        self::decode_query_string();

        $origin = $app->request->headers->get('ORIGIN', '*');

        // Always keep connection open
        $app->response->headers->set('Connection', 'Keep-Alive');

        // Allow Cross-Origin Resource Sharing
        $app->response->headers->set('Access-Control-Allow-Credentials', 'true');
        $app->response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE');
        $app->response->headers->set('Access-Control-Allow-Headers', 'x-app-id, x-app-key, x-auth-token, content-type, user-agent, accept');

        if ($app->request->isOptions()) {
            // Always allow OPTIONS requests.
            $app->response->headers->set('Access-Control-Allow-Origin', $origin);

        } else {
            // Get application key
            $app_key = AppContext::validateKey(
                $app->request->headers->get('X-App-Id') ?: $app->request->get('X-App-Id'),
                $app->request->headers->get('X-App-Key') ?: $app->request->get('X-App-Key')
            );

            if ($app_key) {

                // Check the application key allowed origins, and block if necessary.
                if ($app_key->isBrowser()) {
                    $app->response->headers->set('Access-Control-Allow-Origin', $origin);

                    $request_origin = preg_replace("/https?:\/\//", "", $origin);
                    $allowed_origins = AppConfig::getAll('security.allowed_origins.%', array($request_origin));
                    $is_origin_allowed = array_filter($allowed_origins, function($allowed_origin) use (&$request_origin) {
                        return fnmatch($allowed_origin, $request_origin);
                    });

                    if (count($is_origin_allowed) == 0) {
                        // throw new NotAllowedException("origin_not_allowed");
                        $app->response->setStatus(403); // forbidden
                        $app->response->headers->set('Content-type', 'application/json');
                        $app->response->setBody(json_encode(array('error' => "origin_not_allowed")));
                        return;
                    }
                }

                // Require custom app packages
                Package\Manager::autoload();

                // Compile all route modules
                if ($custom_routes = Module::where('type', Module::TYPE_ROUTE)->get()) {
                    foreach ($custom_routes as $custom_route) {
                        $custom_route->compile();
                    }
                }
            } else if (!\Hook\Controllers\ApplicationController::isRootOperation()) {
                $app->response->setStatus(403);
                $app->response->setBody(json_encode(array('error' => "Invalid credentials.")));

                return;
            }

            //
            // Parse incoming JSON data
            if ($app->request->isPost() || $app->request->isPut() || $app->request->isDelete()) {
                $input_data = $app->environment->offsetGet('slim.input');
                $app->environment->offsetSet('slim.request.form_hash', json_decode($input_data, true));
            }

            return $this->next->call();
        }
    }

}
