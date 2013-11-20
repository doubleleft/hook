<?php

return array('modules' => function($t) {
	$t->increments('_id');
	$t->string('name', 20);
	$t->string('description');
	$t->text('code');
	$t->timestamps();
});
