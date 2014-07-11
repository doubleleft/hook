<?php

return array('apps' => function ($t) {
    $t->increments('_id');
    $t->string('name')->unique();
    $t->string('secret', 40);

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
