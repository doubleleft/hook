<?php

return array('app_keys' => function ($t) {
    $t->increments('_id');
    $t->integer('app_id')->references('_id')->on('apps');
    $t->string('key', 40);
    $t->string('type', 10); // browser / server / device

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
