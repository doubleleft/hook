<?php

namespace scripts;

class GithubKeyDownloader
{
    const API_URL = 'https://api.github.com';
    const DESTINATION_FILE = 'security/.authorized_keys';

    public static function execute()
    {
        // TODO: create configuration file for organization/client_id/client_secret
        $organization = 'doubleleft';

        $data = '# -------------------------------------------------------------' . PHP_EOL;
        $data.= '# WARNING: PROTECT PUBLIC ACCESS OF THIS FILE ON YOUR WEBSERVER' . PHP_EOL;
        $data.= '# -------------------------------------------------------------' . PHP_EOL;
        $data.= '# Lists of allowed openssh keys to consume API via commandline ' . PHP_EOL;
        $data.= '# ' . PHP_EOL;

        \Guzzle\Http\StaticClient::mount();

        $public_keys = array();
        foreach (self::get("/orgs/{$organization}/members") as $member) {
            foreach (self::getUserKeys($member['login']) as $key) {
                $data .= $key['key'] . PHP_EOL;
            }
        }

        file_put_contents(__DIR__ . '/../../' . self::DESTINATION_FILE, $data);
        echo "Github public keys downloaded successfully at '".self::DESTINATION_FILE."'." . PHP_EOL;
    }

    public static function getUserKeys($login)
    {
        return self::get("/users/{$login}/keys");
    }

    protected static function get($segments)
    {
        //
        // Authenticate organization to retrieve full list of members.
        // FIXME: without authentication, only the public list is being retrieved.
        //
        $client_id = '';
        $client_secret = '';

        return \Guzzle::get(self::API_URL . "{$segments}?client_id={$client_id}&client_secret={$client_secret}")->json();
    }
}
