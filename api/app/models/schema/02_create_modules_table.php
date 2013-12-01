<?php

return array('modules' => function($t) {
	$t->increments('_id');
	$t->integer('app_id')->references('_id')->on('apps');
	$t->string('name', 20);
	$t->string('description');
	$t->text('code');
	$t->timestamps();
});
