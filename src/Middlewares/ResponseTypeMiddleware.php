<?php
namespace Hook\Middlewares;

use Slim;
use Exception;

class ResponseTypeMiddleware extends Slim\Middleware
{

    public function call()
    {
        // The Slim application
        $app = $this->app;

        try {
            // Call current request
            var_dump($this->next);
            $response = $this->next->call();

            var_dump($response);
            var_dump($app->response->getBody());

            // Only set body automatically if it wasn't set manually
            if (!$app->response->getBody()) {
                $app->response->setBody($response);
            } else {
                $response = $app->response->getBody();
            }

            // Set content-type automatically if it wasn't set manually
            if (!$app->response->headers->get('Content-type')) {
                $this->autoContentType($app->response);
            }

        } catch (Exception $e) {
            $response = $this->handleErrorRespone($e, $app);
            $app->response->headers->set('Content-type', 'application/json');
            $app->response->setBody(to_json($response));
        }

        // return 404 status code when 'content' is null or false.
        // probably something is wrong. It's better the API shout it for the client.
        if ($response === null || $response === false) {
            $app->response->setStatus(404);
        }

    }

    protected function autoContentType($response) {
        if (gettype($response)=="string") {
            $content_type = 'text/html';
        } else {
            $content_type = 'application/json';
        }

        $response->headers->set('Content-type', $content_type);
    }

    protected function handleErrorRespone($e, $app)
    {
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();

        $app->log->info("Error: '{$message}'");
        $app->log->info($trace);

        try {
            file_put_contents('php://stderr', "[[ hook: error ]] " . $message . PHP_EOL . $trace . PHP_EOL);
        } catch (Exception $e) {
            // echo $message . "<br />";
            // echo nl2br($trace);
        }

        $code = intval($e->getCode());
        if (!$code || $code < 200 || $code > 500) {
            $code = 500;
        }
        $app->response->setStatus($code);

        return array('error' => $message, 'trace' => $trace); //
    }

}
