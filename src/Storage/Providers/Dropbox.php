<?php
namespace Hook\Storage\Providers;

//
// add to your packages.yaml:
// dropbox/dropbox-sdk: 1.1.*
//

use Hook\Application\Config;

use Dropbox as dbx;

class Dropbox extends Base
{
    protected $client;

    public function store($filename, $data, $options = array())
    {
        // Maybe we should use this method instead:
        // getClient()->uploadFileFromString($path, $writeMode, $data)
        $stream = fopen('php://memory','r+');
        fwrite($stream, $data);
        rewind($stream);

        // TODO upload chunked?

        $uploaded = $this->getClient()->uploadFile("/" . $filename, dbx\WriteMode::add(), $stream);
        return $this->getClient()->createShareableLink($uploaded['path']);
    }

    protected function getClient()
    {
        if (!$this->client) {
            $app_access_token = Config::get('storage.access_token');
            $client_identifier = "hook-server/0.2";
            $this->client = new dbx\Client($app_access_token, $client_identifier);
        }

        return $this->client;
    }



}
