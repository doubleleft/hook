<?php

class KeyValue extends TestCase {

	public function testSetString() {
		$this->post('key/some_string', array('value' => 5));
		$key = $this->get('key/some_string');
		$this->assertTrue($key['value'] === '5');
	}

	// TODO
	public function testSetInteger() {
		$this->post('key/some_integer', array('value' => 5));
		$key = $this->get('key/some_integer');
		$this->assertTrue($key['value'] === 5);
	}

	// TODO
	public function testSetFloat() {
		$this->post('key/some_float', array('value' => 9.9));
		$key = $this->get('key/some_float');
		$this->assertTrue($key['value'] === 9.9);
	}

	// TODO
	public function testSetArray() {
		$this->post('key/some_array', array('value' => array('one', 'two', 'three')));
		$key = $this->get('key/some_array');
		$this->assertTrue($key['value'] === array('one', 'two', 'three'));
	}

}

