<?php

return array('auths' => function ($t) {
    $t->increments('_id');
    $t->string('email', 100)->nullable();
    $t->string('password', 40)->nullable();
    $t->string('password_salt', 40)->nullable();
    $t->string('forgot_password_token', 40)->nullable();

    // timestamps
    $t->timestamp('forgot_password_expiration')->nullable();
    $t->softDeletes();
    $t->timestamps();
});

