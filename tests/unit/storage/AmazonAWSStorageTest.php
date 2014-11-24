<?php

use Hook\Storage\Providers\Filesystem;
use Hook\Application\Config;
use Hook\Model\File;

class AmazonAWSStorageTest extends TestCase
{

    public function testStore()
    {
        Config::set('storage.provider', 'amazon_aws');

        //
        // How to mock aws sdk requests:
        // http://docs.aws.amazon.com/aws-sdk-php/guide/latest/feature-facades.html
        //
        $mockS3Client = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->getMock();

        // $file = File::create(array(
        //     'file' => 'data:text/plain;base64,' . base64_encode('Saving text file.')
        // ));
        // $this->assertTrue(preg_match('/^http:\/\//', $file->path) == 1);
        // $this->assertTrue($file->read() == "Saving text file.");
    }

}
