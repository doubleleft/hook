<?php

return array('files' => function($t) {
	$t->increments('_id');
	$t->integer('app_id')->references('_id')->on('apps');
	$t->string('path');
	$t->string('name');
	$t->string('mime');

	// timestamps
	$t->integer('created_at');
	$t->integer('updated_at');
});
