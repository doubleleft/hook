<?php
namespace API\Exceptions;

/**
 * The request requires user authentication.
 *
 * @see ErrorException
 */
class UnauthorizedException extends ErrorException
{
    protected $code = 401;
}

/**
 * The server understood the request, but is refusing to fulfill it.
 *
 * @see ErrorException
 */
class ForbiddenException extends ErrorException
{
    protected $code = 403;
}

/**
 * The server has not found anything matching the Request-URI.
 *
 * @see ErrorException
 */
class NotFoundException extends ErrorException
{
    protected $code = 404;
}

/**
 * MethodFailureException
 *
 * @see ErrorException
 */
class MethodFailureException extends ErrorException
{
    protected $code = 424;
}

/**
 * The server encountered an unexpected condition which prevented it from fulfilling the request.
 *
 * @see ErrorException
 */
class InternalException extends ErrorException
{
    protected $code = 500;
}

/**
 * The server does not support the functionality required to fulfill the request.
 *
 * @see ErrorException
 */
class NotImplementedException extends ErrorException
{
    protected $code = 501;
}

/**
 * The server is currently unable to handle the request due to a
 * temporary overloading or maintenance of the server.
 *
 * @see ErrorException
 */
class ServiceUnavailableException extends ErrorException
{
    protected $code = 503;
}
