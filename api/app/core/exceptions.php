<?php

class UnauthorizedException extends ErrorException {
	protected $code = 401;
}

class ForbiddenException extends ErrorException {
	protected $code = 403;
}

class NotFoundException extends ErrorException {
	protected $code = 404;
}

class MethodFailureException extends ErrorException {
	protected $code = 424;
}
