<?php

class ForbiddenException extends ErrorException {
	protected $code = 403;
}

class UnauthorizedException extends ErrorException {
	protected $code = 401;
}

class MethodFailureException extends ErrorException {
	protected $code = 424;
}
