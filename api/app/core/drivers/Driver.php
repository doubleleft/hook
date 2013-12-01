<?php

namespace Core\Drivers;

class Driver {
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

}
