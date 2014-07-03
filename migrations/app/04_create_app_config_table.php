<?php

return array('app_configs' => function ($t) {
    $t->increments('_id');
    $t->string('name');
    $t->text('value');

    // timestamps
    $t->integer('created_at');
    $t->integer('updated_at');
});
