<?php

return array('app_keys' => function ($t) {
    $t->increments('_id');
    $t->integer('app_id')->references('_id')->on('apps');
    $t->string('key');
    // $t->string('secret');
    $t->boolean('admin')->default(false);

    // timestamps
    $t->integer('created_at');
    $t->integer('updated_at');
});
