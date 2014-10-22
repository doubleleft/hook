<?php
namespace Hook\Middlewares;

use Exception;

class ChannelMiddleware extends ResponseTypeMiddleware
{
    const MAX_POOLING_RETRY = 5; // 5 seconds
    const MIN_POOLING_RETRY = 1; // 1 second

    const MAX_REFRESH_TIMEOUT = 4; // 4 seconds
    const MIN_REFRESH_TIMEOUT = 1; // 1 seconds

    public function call()
    {
        // The Slim application
        $app = $this->app;

        //
        // Respond based on ACCEPT request header
        // Add EventSource middeware: http://en.wikipedia.org/wiki/Server-sent_events | http://www.html5rocks.com/en/tutorials/eventsource/basics/
        //
        if (($app->request->headers->get('ACCEPT') == 'text/event-stream') || // Checking for ACCEPT header is smarter.
             $app->request->getMethod() == 'GET' && preg_match('/^\/channels/', $app->request->getResourceUri())) { // Workaround for Internet Explorer, which can't send custom request headers on CORS requests.
            ini_set('zlib.output_compression', 0);
            ini_set('implicit_flush', 1);

            // Start buffering
            ob_start();

            $pool_start = $app->request->headers->get('X-Time') ?: time();

            // stream timing configs
            $stream_config = $app->request->get('stream');
            $refresh_timeout = (isset($stream_config['refresh'])) ? intval($stream_config['refresh']) : self::MIN_REFRESH_TIMEOUT;
            $refresh_timeout = clamp($refresh_timeout, self::MIN_REFRESH_TIMEOUT, self::MAX_REFRESH_TIMEOUT);
            $retry_timeout = ((isset($stream_config['retry'])) ? intval($stream_config['retry']) : self::MIN_POOLING_RETRY);
            $retry_timeout = clamp($retry_timeout, self::MIN_POOLING_RETRY, self::MAX_POOLING_RETRY) * 1000;

            $last_event_id = $app->request->headers->get('Last-Event-ID') ?: $app->request->get('lastEventId');

            // Set response headers
            $app->response->headers->set('Content-type', 'text/event-stream');
            $app->response->headers->set('Cache-Control', 'no-cache');
            foreach ($app->response->headers as $header => $content) {
                header("{$header}: {$content}");
            }

            echo 'retry: '. $retry_timeout . PHP_EOL . PHP_EOL;

            do {
                // Close EventSource connection after 15 seconds
                // let the client re-open it if necessary
                if ((time() - $pool_start) > 15) {
                    die();
                }

                // Append last-event-id to filtering options
                if ($last_event_id) {
                    $query_data = AppMiddleware::decode_query_string();
                    if (!isset($query_data['q'])) {
                        $query_data['q'] = array();
                    }

                    if ($last_event_id) {
                        array_push($query_data['q'], array('_id', '>', $last_event_id));
                    }

                    $app->environment->offsetSet('slim.request.query_hash', $query_data);
                }

                try {
                    // Call current request
                    $this->next->call();
                    $response = $app->response->getBody();
                } catch (Exception $e) {
                    $response = $this->handleErrorResponse($e, $app);
                }

                // Multiple results
                if (method_exists($response, 'each')) {
                    $self = $this;
                    $response->each(function ($data) use ($app, &$last_event_id, &$self) {
                        echo 'id: '. $data->_id . PHP_EOL . PHP_EOL;
                        echo 'data: '. to_json($data) . PHP_EOL . PHP_EOL;
                        ob_flush();
                        flush();
                        $last_event_id = $data->_id;
                    });

                } else {
                    // Single result
                    if ($response instanceof stdClass) {
                        echo 'id: '. $response->_id . PHP_EOL . PHP_EOL;
                        $last_event_id = $data->content->_id;
                    }
                    echo 'data: '. to_json($response) . PHP_EOL . PHP_EOL;
                    ob_flush();
                    flush();
                }

                sleep($refresh_timeout);
            } while (true);

         } else {
             return $this->next->call();
         }
    }

    protected function handleErrorResponse($e, $app)
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
