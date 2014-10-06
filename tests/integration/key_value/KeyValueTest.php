<?php

class KeyValue extends HTTP_TestCase
{
    public function testString()
    {
        $this->post('key/some_string', array('value' => 'Hello world'));
        $value = $this->get('key/some_string');
        $this->assertTrue($value === 'Hello world');
    }

    public function testInteger()
    {
        $this->post('key/some_integer', array('value' => 5));
        $value = $this->get('key/some_integer');
        $this->assertTrue($value === 5, 'int 5 results as "5"');
    }

    public function testFloat()
    {
        $this->post('key/some_float', array('value' => 9.9));
        $value = $this->get('key/some_float');
        $this->assertTrue($value === 9.9, 'float 9.9 results as "9.9"');
    }

    public function testArray()
    {
        $response = $this->post('key/some_array', array('value' => array('one', 'two', 'three')));
        $value = $this->get('key/some_array');
        $this->assertTrue($value === array('one', 'two', 'three'), "array ['one', 'two', 'three'] ok.");
    }

    public function testNested()
    {
        $response = $this->post('key/nested', array('value' => array(
            'nested' => array(
                'complex' => 999,
                'fields' => "are",
                'supported' => "too!"
            ),
        )));
        $value = $this->get('key/nested');

        $this->assertTrue($value === array(
            'nested' => array(
                'complex' => 999,
                'fields' => "are",
                'supported' => "too!"
            ),
        ), "nested key/value ok.");
    }

}
