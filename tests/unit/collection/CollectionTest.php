<?php

use Hook\Model\App as App;

class CollectionTest extends TestCase
{

    public function testCreate()
    {
        $item = App::collection('names')->create(array(
            'name' => "Endel",
            'number' => 10,
            'double' => 9.99
        ));
        $this->assertTrue($item->name == "Endel");
        $this->assertTrue($item->number == 10);
        $this->assertTrue($item->double == 9.99);

        $row = App::collection('names')->sort('created_at', -1)->first();
        $this->assertTrue($row->name == "Endel");
        $this->assertTrue($row->number == 10);
        $this->assertTrue($row->double == 9.99);
    }

    public function testBulkCreate() {
        // $items = App::collection('names')->create(array(
        //     array(
        //         'name' => "Bulk 1",
        //         'number' => 1,
        //         'double' => 1.1
        //     ),
        //     array(
        //         'name' => "Bulk 2",
        //         'number' => 2,
        //         'double' => 2.2
        //     )
        // ));
        //
        // $this->assertTrue(count($items) == 2);
    }

}
