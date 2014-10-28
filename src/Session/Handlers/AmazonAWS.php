<?php namespace Hook\Session\Handlers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Session\SessionHandler;
use Aws\DynamoDb\Session\SessionHandlerConfig;
use Aws\DynamoDb\Session\LockingStrategy\LockingStrategyFactory;

// DynamoDB
// http://docs.aws.amazon.com/aws-sdk-php/guide/latest/feature-dynamodb-session-handler.html
class AmazonAWS extends SessionHandler {
    public function __construct() {
        $bucket = Config::get('storage.bucket', 'default');
        $key = Config::get('storage.key');
        $secret = Config::get('storage.secret');

        $client = DynamoDbClient::factory(array(
            'key'    => $key,
            'secret' => $secret,
            'region' => '<region name>',
        ));

        $config = new SessionHandlerConfig(array(
            'table_name' => 'sessions',
        ));

        // Make sure locking strategy has been provided or provide a default
        $factory  = new LockingStrategyFactory();
        $strategy = $factory->factory($strategy, $config);

        // Return an instance of the session handler
        parent::__construct($client, $strategy, $config);
    }
}
