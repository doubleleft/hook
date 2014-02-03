<?php

return array('auth_tokens' => function($t) {
	$t->increments('_id');
	$t->integer('app_id')->references('_id')->on('apps');
	$t->integer('auth_id')->references('_id')->on('auth');
	$t->string('token');
	$t->dateTime('expire_at');
	$t->dateTime('created_at');
});
