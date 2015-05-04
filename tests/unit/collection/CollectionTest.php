<?php

use Hook\Model\App as App;

class CollectionTest extends TestCase
{

    public function testCreate()
    {
        $my_item = App::collection('my_items')->create(array('name' => "One"));
        $this->assertTrue($my_item['name'] == "One");

        $my_item_2 = App::collection('my_items')->create(array('name' => "Two"));
        $this->assertTrue($my_item_2['name'] == "Two");
    }

    public function testUpdateMultiple() {
        $my_item = App::collection('my_items')->create(array('name' => "Three"));
        $this->assertTrue($my_item['name'] == "Three");

        App::collection('my_items')->update(array('ignore_me' => null, 'create_me' => 10));
        $my_item = App::collection('my_items')->first();
        $this->assertTrue($my_item->create_me == 10);
    }

    public function testCache() {
        App::collection('my_items')->create(array('name' => "Cached"));
        $first_item = App::collection('my_items')->where('name', "Cached")->remember(10)->get();
        $this->assertTrue($first_item[0]->name == "Cached");

        App::collection('my_items')->update(array('name' => "Not cached!"));

        $first_item = App::collection('my_items')->where('name', "Cached")->remember(10)->get();
        $this->assertTrue($first_item[0]->name == "Cached");

    }

}
