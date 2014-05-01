<?php

class CollectionCrud extends TestCase {

	public function testCreate() {
		$created = $this->post('collections/something', array(
			'name' => "Name",
			'floating' => 9.9,
			'float_string' => '9.9',
			'integer' => 1
		));
		$this->assertTrue(is_array($created), "Created 'something' successfully");
	}

}
