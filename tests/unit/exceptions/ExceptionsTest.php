<?php
use Hook\Exceptions;

class ExceptionsTest extends TestCase
{

    /**
     * @expectedException Hook\Exceptions\UnauthorizedException
     * @expectedExceptionMessage message
     */
    public function testDataTypes()
    {
        throw new Exceptions\UnauthorizedException("message");
    }

}

