<?php

return array('cache' => function ($t) {
    $t->string('key')->unique();
    $t->text('value');
    $t->integer('expiration');
});
