<?php
use Hook\Exceptions as E;

class ExceptionsTest extends TestCase
{

    public function testMessages()
    {
        $bad_request = new E\BadRequestException();
        $this->assertTrue($bad_request->getMessage() == "bad_request");

        $custom_bad_request = new E\BadRequestException("custom_bad_request");
        $this->assertTrue($custom_bad_request->getMessage() == "custom_bad_request");

        $unauthorized = new E\UnauthorizedException();
        $this->assertTrue($unauthorized->getMessage() == "unauthorized");

        $forbidden = new E\ForbiddenException();
        $this->assertTrue($forbidden->getMessage() == "forbidden");

        $not_found = new E\NotFoundException();
        $this->assertTrue($not_found->getMessage() == "not_found");

        $not_allowed = new E\NotAllowedException();
        $this->assertTrue($not_allowed->getMessage() == "not_allowed");

        $method_failure = new E\MethodFailureException();
        $this->assertTrue($method_failure->getMessage() == "method_failure");

        $internal_error = new E\InternalException();
        $this->assertTrue($internal_error->getMessage() == "internal_error");

        $not_implemented = new E\NotImplementedException();
        $this->assertTrue($not_implemented->getMessage() == "not_implemented");

        $service_unavailable = new E\ServiceUnavailableException();
        $this->assertTrue($service_unavailable->getMessage() == "service_unavailable");
    }

}
