<?php

return array('modules' => function ($t) {
    $t->increments('_id');
    $t->integer('app_id')->references('_id')->on('apps');
    $t->string('name')->nullable();
    $t->string('type')->nullable();
    $t->string('description')->nullable();
    $t->text('code');

    // timestamps
    $t->integer('created_at');
    $t->integer('updated_at');
});
