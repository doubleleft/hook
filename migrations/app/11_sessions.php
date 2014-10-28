<?php

return array('__sessions' => function ($t) {
    $t->string('id')->unique();
    $t->text('payload');
    $t->integer('last_activity');
});
