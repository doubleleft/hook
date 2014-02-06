<?php
$app = Slim\Slim::getInstance();

/**
 * {method_uppercase} {path}
 */
$app->{method}('{path}', function({arguments}) {
	//
	// do something
	//
	$this->content = array('success' => true);
});
