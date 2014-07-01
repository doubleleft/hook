<?php
namespace API\Exceptions;
use \Exception as Exception;

// Exception descriptions extracted from:
// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html

/**
 * The request could not be understood by the server due to malformed syntax.
 * The client SHOULD NOT repeat the request without modifications.
 *
 * @see Exception
 */
class BadRequestException extends Exception
{
    protected $code = 400;
}

/**
 * The request requires user authentication.
 *
 * @see Exception
 */
class UnauthorizedException extends Exception
{
    protected $code = 401;
}

/**
 * The server understood the request, but is refusing to fulfill it.
 *
 * @see Exception
 */
class ForbiddenException extends Exception
{
    protected $code = 403;
}

/**
 * The server has not found anything matching the Request-URI.
 *
 * @see Exception
 */
class NotFoundException extends Exception
{
    protected $code = 404;
}

/**
 * MethodFailureException
 *
 * @see Exception
 */
class MethodFailureException extends Exception
{
    protected $code = 424;
}

/**
 * The server encountered an unexpected condition which prevented it from fulfilling the request.
 *
 * @see Exception
 */
class InternalException extends Exception
{
    protected $code = 500;
}

/**
 * The server does not support the functionality required to fulfill the request.
 *
 * @see Exception
 */
class NotImplementedException extends Exception
{
    protected $code = 501;
}

/**
 * The server is currently unable to handle the request due to a
 * temporary overloading or maintenance of the server.
 *
 * @see Exception
 */
class ServiceUnavailableException extends Exception
{
    protected $code = 503;
}
