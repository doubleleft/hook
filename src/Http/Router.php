<?php namespace Hook\Http;

use Hook\Middlewares;

class Router {
    protected static $instance;

    /**
     * Bind controller methods into a path
     *
     * @param string $path
     * @param string $controller_klass
     *
     * @example
     *
     *     class MyController {
     *         // Mount callback
     *         public static function mounted($path) {
     *             var_dump("Successfully mounted at " . $path);
     *         }
     *
     *         // GET /path
     *         function getIndex() {}
     *
     *         // POST /path/create
     *         function postCreate() {}
     *
     *         // PUT /path/update
     *         function putUpdate() {}
     *
     *         // DELETE /path
     *         function deleteIndex() {}
     *     }
     *     Hook\Http\Router::mount('/', 'MyController');
     *
     */
    public static function mount($path, $controller_klass)
    {
        $mounted = null;
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
                $mounted = call_user_func(array($controller_klass, 'mounted'), $path);
                continue;
            }

            preg_match_all('/^(get|put|post|patch|delete)(.*)/', $method_name, $matches);
            $has_matches = (count($matches[1]) > 0);

            $http_method = $has_matches ? $matches[1][0] : 'any';
            $route_name = $has_matches ? $matches[2][0] : $method_name;

            $route = str_finish($path, '/');
            if ($route_name !== 'index') {
                $route .= snake_case($route_name);
            }

            static::$instance->{$http_method}($route, "{$controller_klass}:{$method_name}");
        }

        return $mounted;
    }

    /**
     * @nodoc
     */
    public static function setInstance($instance) {
        static::$instance = $instance;
    }

    /**
     * Get Slim application instance
     * @return Slim\Slim
     */
    public static function getInstance() {
        return static::$instance;
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(static::$instance, $method), $arguments);
    }

}
