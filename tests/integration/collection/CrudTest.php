<?php

class CollectionCrud extends HTTP_TestCase
{
    public function testCreate()
    {
        $item = $this->post('collection/something', array(
            'name' => "Name",
            'floating' => 9.9,
            'float_string' => '9.9',
            'integer' => 1
        ));
        $this->assertTrue(is_array($item), "Created 'something' successfully");
        $this->assertTrue($item['name'] == "Name");
        $this->assertTrue($item['floating'] === 9.9);
        $this->assertTrue($item['float_string'] === '9.9');
        $this->assertTrue($item['integer'] === 1);
    }

    public function testDelete()
    {
        $item = $this->post('collection/something', array(
            'name' => "testDelete",
        ));
        $this->assertTrue(is_array($item), "Created item to delete");

        $deleted = $this->delete('collection/something/'. $item['_id']);
        $this->assertTrue($deleted['success'] === true);
    }

    public function testUpdate()
    {
        $item = $this->post('collection/something', array(
            'name' => "testUpdate",
        ));
        $updated_item = $this->post('collection/something/'. $item['_id'], array(
            'floating' => 9.9,
            'float_string' => '9.9',
            'integer' => 1
        ));
        $this->assertTrue($updated_item['floating'] === 9.9, "keep floats on update");
        $this->assertTrue($updated_item['float_string'] === '9.9', "keep strings on update");
        $this->assertTrue($updated_item['integer'] === 1, "keep integer on update");
    }

    public function testUpdateMany()
    {
        $this->post('collection/products', array('name' => "Product 1", 'price' => 1));
        $this->post('collection/products', array('name' => "Product 2", 'price' => 2));
        $this->post('collection/products', array('name' => "Product 3", 'price' => 3));
        $this->post('collection/products', array('name' => "Product 4", 'price' => 4));
        $this->post('collection/products', array('name' => "Product 5", 'price' => 5));

        $updated = $this->put('collection/products', array(
            'q' => array(array('price', '>', 3)),
            'd' => array('price' => 0)
        ));
        $this->assertTrue($updated['affected'] === 2, "update many, with filters");
    }

}
