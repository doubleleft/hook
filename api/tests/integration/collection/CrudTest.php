<?php

class CollectionCrud extends TestCase {

	public function testCreate() {
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

	public function testDelete() {
		$item = $this->post('collection/something', array(
			'name' => "testDelete",
		));
		$this->assertTrue(is_array($item), "Created item to delete");

		$deleted = $this->delete('collection/something/'. $item['_id']);
		$this->assertTrue($deleted['success'] === true);
	}

	public function testUpdate() {
		$item = $this->post('collection/something', array(
			'name' => "testUpdate",
		));
		$updated = $this->put('collection/something/'. $item['_id'], array(
			'floating' => 9.9,
			'float_string' => '9.9',
			'integer' => 1
		));
		$this->assertTrue($updated['success'] === true, "Updated 'something' successfully");
		$updated_item = $this->get('collection/something/'. $item['_id']);
		$this->assertTrue($updated_item['floating'] === 9.9, "keep floats on update");
		$this->assertTrue($updated_item['float_string'] === '9.9', "keep strings on update");
		$this->assertTrue($updated_item['integer'] === 1, "keep integer on update");
	}

}
