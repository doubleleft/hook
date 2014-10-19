<?php

use Hook\Application\Config;

class ConfigDeployTest extends TestCase
{

    public function testConfigDeploy()
    {
        Config::deploy(array(
            'something' => array(
                'very' => array(
                    'deep' => array(
                        'here' => 'value'
                    ),
                    'nice' => 6
                )
            ),
            'another' => '10',
            'hello' => array(
                'there' => 'hey!'
            )
        ));

        $this->assertEquals(Config::get('something.very.deep.here'), 'value');
        $this->assertEquals(Config::get('something.very.nice'), 6);
        $this->assertEquals(Config::get('another'), '10');
        $this->assertEquals(Config::get('hello.there'), 'hey!');
    }

}

