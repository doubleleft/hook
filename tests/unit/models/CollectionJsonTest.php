<?php
use Hook\Model\App;
use Hook\Database\Schema\Cache as SchemaCache;

class CollectionJsonTest extends TestCase
{

    public function testDataTypes()
    {
        App::collection('dummy')->create(array(
            'boolean' => true,
            'number' => 1,
            'float' => 5.5,
            'string' => "Strings"
        ));

        $data = App::collection('dummy')->first()->toArray();

        $this->assertTrue(is_int($data['number']));
        $this->assertTrue(is_float($data['float']));
        $this->assertTrue(is_string($data['string']));
        $this->assertTrue(is_bool($data['boolean']));
    }

}
