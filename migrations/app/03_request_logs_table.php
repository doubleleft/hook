<?php

return array('request_logs' => function ($t) {
    $t->increments('_id');
    $t->string('key_id');
    $t->string('uri');
    $t->string('method', 6);

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
