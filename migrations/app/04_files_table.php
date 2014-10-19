<?php

return array('files' => function ($t) {
    $t->increments('_id');
    $t->string('path');
    $t->string('name');
    $t->string('mime');

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
