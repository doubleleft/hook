<?php
namespace Hook\Middlewares;

use Slim;
use Hook\Logger\LogWriter;
use Hook\Application\Context as Context;

class LogMiddleware extends Slim\Middleware
{
    public function call()
    {
        $app = $this->app;
        $app_key = Context::getKey();

        //
        // TODO: need a way to enable/disable logs for production use
        //

        // Log all queries
        $dispatcher = \Hook\Model\Collection::getEventDispatcher();
        $dispatcher->listen('illuminate.query', function($query, $bindings, $time, $name) use (&$app) {
            $data = compact('bindings', 'time', 'name');

            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding) {
                if ($binding instanceof \DateTime) {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } else if (is_string($binding)) {
                    $bindings[$i] = "'$binding'";
                }
            }

            // Insert bindings into query
            $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
            $query = vsprintf($query, $bindings);

            $app->log->info($query);
        });

        if (!$app->request->isOptions() && $app_key) {
            // set application log writer for this app
            $log_file = storage_dir() . '/logs.txt';
            $app->log->setWriter(new LogWriter($log_file));

            // disable log if storage directory doesn't exists.
            // maybe we're on a readonly filesystem
            $app->log->setEnabled(file_exists($log_file));

            if (strpos($app->request->getPath(), "/apps/") === false) {
                $app->log->info($app->request->getIp() . ' - [' . date('d-m-Y H:i:s') . '] ' . $app->request->getMethod() . ' ' . $app->request->getResourceUri());
                $app->log->info('Params: ' . json_encode($app->request->params()));
            }
        }

        return $this->next->call();
    }
}
