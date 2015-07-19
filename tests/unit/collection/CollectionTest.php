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

    public function testUpdateMultiple() {
        $my_item = App::collection('my_awesome_items')->create(array('name' => "Three"));
        $this->assertTrue($my_item['name'] == "Three");

        App::collection('my_awesome_items')->update(array('ignore_me' => null, 'create_me' => 10));
        $my_item = App::collection('my_awesome_items')->first();
        $this->assertTrue($my_item->create_me == 10);
    }

    public function testCache() {
        $this->markTestIncomplete("This feature doesn't work yet.");

        App::collection('my_items')->create(array('name' => "Cached"));
        $first_item = App::collection('my_items')->where('name', "Cached")->remember(10)->get();
        $this->assertTrue($first_item[0]->name == "Cached");

        App::collection('my_items')->update(array('name' => "Not cached!"));

        $first_item = App::collection('my_items')->where('name', "Cached")->remember(10)->get();
        $this->assertTrue($first_item[0]->name == "Cached");
    }

    public function testBulkCreate() {
        $models = App::collection('my_items')->create(array(
            array('name' => "One"),
            array('name' => "Two"),
            array('name' => "Three")
        ));
        $this->assertTrue($models[0]->name == "One");
        $this->assertTrue($models[1]->name == "Two");
        $this->assertTrue($models[2]->name == "Three");
    }

}
