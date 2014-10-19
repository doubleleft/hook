<?php
namespace Hook\Storage\Providers;

//
// add to your packages.yaml:
// aws/aws-sdk-php: dev-master
//

use Hook\Application\Config;

use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;

class AmazonAWS extends Base
{
    protected $client;

    public function store($filename, $data, $options = array()) {
        $object = $this->getClient()->putObject(array(
            'Bucket' => Config::get('storage.bucket', 'default'),
            'Key' => $filename,
            'Body' => $data,
            'ContentType' => $options['mime'],
            'ACL' => 'public-read',
        ));
        return $object['ObjectURL'];
    }

    protected function getClient() {
        if (!$this->client) {
            $bucket = Config::get('storage.bucket', 'default');
            $key = Config::get('storage.key');
            $secret = Config::get('storage.secret');

            $this->client = Aws::factory(array(
                'bucket' => $bucket,
                'key' => $key,
                'secret' => $secret
            ))->get('s3');
        }
        return $this->client;
    }

}
