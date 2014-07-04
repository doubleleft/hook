<?php

return array('app_configs' => function ($t) {
    $t->increments('_id');
    $t->string('name');
    $t->text('value');

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
