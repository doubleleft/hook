<?php

return array(
	'apps' => function($t) {
		$t->increments('_id');
		$t->string('name');

		// timestamps
		$t->integer('created_at');
		$t->integer('updated_at');
	}
);
