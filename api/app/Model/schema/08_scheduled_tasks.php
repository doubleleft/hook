<?php

return array('scheduled_tasks' => function($t) {
	$t->increments('_id');
	$t->integer('app_id')->references('_id')->on('apps');
	$t->string('schedule');
	$t->string('task');

	// timestamps
	$t->integer('created_at');
	$t->integer('updated_at');
});

