<?php
namespace Hook\Storage\Providers;

//
// add to your packages.yaml:
// aws/aws-sdk-php: dev-master
//

use Aws\S3\S3Client;

class AmazonAWS extends Base
{

    public function store($filename, $data, $options = array()) {
        $client = $this->getS3Client();
    }

    protected function getS3Client() {
        return S3Client::factory(array(
        ));
    }

}
