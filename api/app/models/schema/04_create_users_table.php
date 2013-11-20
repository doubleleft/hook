<?php

return array('users' => function($t) {
	$t->increments('_id');
	$t->string('email');
	$t->string('password');
	$t->string('salt');
	$t->timestamps();
});
