<?php

return array('app_keys' => function ($t) {
    $t->increments('_id');
    $t->integer('app_id')->references('_id')->on('apps');
    $t->string('key', 40);
    $t->boolean('admin')->default(false);

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
