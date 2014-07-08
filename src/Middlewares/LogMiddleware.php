<?php
namespace Hook\Middlewares;

use Slim;
use Hook\Logger\LogWriter;
use Hook\Database\AppContext as AppContext;

class LogMiddleware extends Slim\Middleware
{
    public function call()
    {
        $app = $this->app;
        $app_key = AppContext::getKey();

        //
        // TODO: find a way to enable/disable logs for production use
        //
        if (!$app->request->isOptions() && $app_key) {
            // set application log writer for this app
            $app->log->setWriter(new LogWriter(storage_dir() . '/logs.txt'));

            if (strpos($app->request->getPath(), "/apps/") === false) {
                $app->log->info($app->request->getIp() . ' - [' . date('d-m-Y H:i:s') . '] ' . $app->request->getMethod() . ' ' . $app->request->getResourceUri());
                $app->log->info('Params: ' . json_encode($app->request->params()));
            }
        }

        $this->next->call();
    }
}
