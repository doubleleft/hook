<?php

use Hook\Encryption\Encrypter as Encrypter;

class EncrypterTest extends TestCase
{

    public function testEncryptDecrypt()
    {
        $encrypter = Encrypter::getInstance();
        $encrypted = $encrypter->encrypt("that's important data here");
        $this->assertTrue(strlen($encrypted) == 60);
        $this->assertTrue($encrypter->decrypt($encrypted) == "that's important data here");
    }

}
