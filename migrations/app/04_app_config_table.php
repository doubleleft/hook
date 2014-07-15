<?php

return array('app_configs' => function ($t) {
    $t->string('name')->index()->unique();
    $t->text('value');

    // timestamps
    $t->softDeletes();
    $t->timestamps();
});
