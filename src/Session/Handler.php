<?php namespace Hook\Session;

class Handler {
    // available session handlers
    static $handlers = array(
        'amazon_aws' => "Hook\\Session\\Handlers\\AmazonAWS",
        'database' => "Hook\\Session\\Handlers\\Database",
        'memory' => "Hook\\Session\\Handlers\\Memory",
        'redis' => "Hook\\Session\\Handlers\\Redis",
        'windows_azure' => "Hook\\Session\\Handlers\\WindowsAzure",
    );

    public static function register() {
        return new self::$list[$name];
    }
}

