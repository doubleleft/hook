<?php namespace Hook\Session\Handlers;

//
// add to your packages.yaml:
// predis/predis: 1.0.0
//

use Hook\Application\Config;
use Predis\Session\Handler;

// Example: https://github.com/nrk/predis/blob/d26db6f0c2fa135898d125dad6826fb6ee70d3e1/examples/session_handler.php
class Redis extends Handler {

    public function __construct() {
        $config = Config::get('redis');

        if (!$config) {
            throw new ServiceUnavailableException("'redis' config key missing.");
        }

        $client = new Predis\Client($config, array('prefix' => 'sessions:'));

        // Set `gc_maxlifetime` to specify a time-to-live of 5 seconds for session keys.
        parent::__construct($client); // , array('gc_maxlifetime' => 5)
    }

}
