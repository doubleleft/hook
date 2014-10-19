<?php

return array('scheduled_tasks' => function ($t) {
    $t->increments('_id');
    $t->string('schedule');
    $t->string('task');

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
