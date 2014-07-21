<?php

use Hook\Model\AppConfig as AppConfig;

class ConfigDeployTest extends TestCase
{

    public function testConfigDeploy()
    {
        AppConfig::truncate();
        AppConfig::deploy(array(
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

        $this->assertEquals(AppConfig::get('something.very.deep.here'), 'value');
        $this->assertEquals(AppConfig::get('something.very.nice'), 6);
        $this->assertEquals(AppConfig::get('another'), '10');
        $this->assertEquals(AppConfig::get('hello.there'), 'hey!');
    }

}

