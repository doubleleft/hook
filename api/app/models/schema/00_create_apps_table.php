<?php

return array(
	'apps' => function($t) {
		$t->increments('_id');
		$t->string('name');
		$t->timestamps();
	}
);
