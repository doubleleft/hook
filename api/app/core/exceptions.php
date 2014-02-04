<?php

class ForbiddenException extends Exception {
	protected $code = 403;
}

class UnauthorizedException extends Exception {
	protected $code = 401;
}

class MethodFailureException extends Exception {
	protected $code = 424;
}
