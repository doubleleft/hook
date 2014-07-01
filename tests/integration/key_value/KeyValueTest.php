<?php

class KeyValue extends HTTP_TestCase
{
    public function testSetString()
    {
        $this->post('key/some_string', array('value' => 5));
        $key = $this->get('key/some_string');
        $this->assertTrue($key['value'] === '5');
    }

    public function testSetInteger()
    {
        $this->post('key/some_integer', array('value' => 5));
        $key = $this->get('key/some_integer');
        $this->assertTrue($key['value'] === '5', 'int 5 results as "5"');
    }

    public function testSetFloat()
    {
        $this->post('key/some_float', array('value' => 9.9));
        $key = $this->get('key/some_float');
        $this->assertTrue($key['value'] === '9.9', 'float 9.9 results as "9.9"');
    }

    public function testSetArray()
    {
        $response = $this->post('key/some_array', array('value' => array('one', 'two', 'three')));
        $this->assertTrue(isset($response['error']), "array key-values are not supported.");
        // $key = $this->get('key/some_array');
        // $this->assertTrue($key['value'] === array('one', 'two', 'three'));
    }

}
