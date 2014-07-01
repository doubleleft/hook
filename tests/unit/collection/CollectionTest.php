<?php

use API\Model\App as App;

class CollectionTest extends TestCase
{

    public function testCreate()
    {
        App::collection('testing')->each(function($row) {
            var_dump($row);
        });
    }

}
