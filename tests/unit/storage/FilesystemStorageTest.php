<?php

use Hook\Storage\Providers\Filesystem;
use Hook\Application\Config;
use Hook\Model\File;

class FilesystemStorageTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Config::set('storage.provider', 'filesystem');
    }

    public function testStore()
    {
        $file = File::create(array(
            'file' => 'data:text/plain;base64,' . base64_encode('Saving text file.')
        ));
        $this->assertTrue(preg_match('/^http:\/\//', $file->path) == 1);
        $this->assertTrue($file->read() == "Saving text file.");
    }

}
