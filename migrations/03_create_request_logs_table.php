<?php

return array('request_logs' => function ($t) {
    $t->increments('_id');
    $t->integer('app_id')->references('_id')->on('apps');
    $t->string('key_id');
    $t->string('uri');
    $t->string('method', 6);

    // timestamps
    $t->integer('created_at');
    $t->integer('updated_at');
});
