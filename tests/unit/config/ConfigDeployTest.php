<?php

use Hook\Model\AppConfig as AppConfig;

class ConfigDeployTest extends TestCase
{

    public function testConfigDeploy()
    {
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

        $configs = AppConfig::all();

        $this->assertTrue($configs[0]->name == 'something.very.deep.here');
        $this->assertTrue($configs[0]->value == 'value');

        $this->assertTrue($configs[1]->name == 'something.very.nice');
        $this->assertTrue($configs[1]->value == 6);

        $this->assertTrue($configs[2]->name == 'another');
        $this->assertTrue($configs[2]->value == '10');

        $this->assertTrue($configs[3]->name == 'hello.there');
        $this->assertTrue($configs[3]->value == 'hey!');
    }

}

