<?php

return array('auth_tokens' => function ($t) {
    $t->increments('_id');
    $t->integer('auth_id')->references('_id')->on('auth');
    $t->string('token');
    $t->integer('role')->default(0);

    // timestamps
    $t->timestamp('created_at');
    $t->timestamp('expire_at');
});
