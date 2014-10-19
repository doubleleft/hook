<?php

return array('auth_identities' => function ($t) {
    $t->increments('_id');
    $t->integer('auth_id');
    $t->string('provider', 20);
    $t->string('uid', 60);

    // timestamps
    $t->softDeletes();
    $t->timestamps();

    // ensure provider / uid uniqueness
    $t->unique(array('provider', 'uid'));
});


