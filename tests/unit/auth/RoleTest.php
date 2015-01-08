<?php

use Hook\Auth\Role;
use Hook\Model\App;
use Hook\Model\AuthToken;
use Hook\Model\AppKey;
use Hook\Application\Config;

use Hook\Application\Context;

class RoleTest extends TestCase {
    protected $defaults;
    protected $lastKey;

    public function __construct()
    {
        parent::__construct();

        $role = Role::getInstance();
        $this->defaults = $role->getDefaultConfig();
    }

    public function setUp()
    {
        // only browser/server/device keys are affected by Role system.
        AppKey::current()->type = AppKey::TYPE_BROWSER;
    }

    public function tearDown()
    {
        // restore commandline key
        AppKey::current()->type = AppKey::TYPE_CLI;
    }

    public function testDefaults()
    {
        $this->assertTrue( Role::isAllowed('collection_name', 'read') );
        $this->assertTrue( Role::isAllowed(App::collection('collection_name'), 'read') );

        $this->assertTrue( Role::isAllowed('collection_name', 'create') );
        $this->assertTrue( Role::isAllowed(App::collection('collection_name'), 'create') );

        $this->assertFalse( Role::isAllowed('collection_name', 'update') );
        $this->assertFalse( Role::isAllowed(App::collection('collection_name'), 'update') );

        $this->assertFalse( Role::isAllowed('collection_name', 'delete') );
        $this->assertFalse( Role::isAllowed(App::collection('collection_name'), 'delete') );
    }

    /**
     * @expectedException Hook\Exceptions\NotAllowedException
     * @expectedExceptionMessage not_allowed
     */
    public function testOwnerReadException()
    {
        $this->setConfig(App::collection('restricted_content')->getTable(), 'read', 'owner');

        App::collection('restricted_content')->create(array('name' => "Read exception"));
        App::collection('restricted_content')->first()->toArray();
        var_dump(App::collection('restricted_content')->first()->toArray());
    }

    public function testOwnerReadSuccess()
    {
        $this->setConfig(App::collection('restricted_content')->getTable(), 'read', 'owner');

        $auth_id = 1;
        App::collection('restricted_content')->create(array(
            'name' => "Read success",
            'auth_id' => $auth_id
        ));
        App::collection('restricted_content')->create(array(
            'name' => "Read fail",
            'auth_id' => 2
        ));

        // mock authorized user
        $auth_token = new AuthToken(array('auth_id' => $auth_id));
        AuthToken::setCurrent($auth_token);

        $this->assertTrue(is_array(App::collection('restricted_content')->where('auth_id', 1)->first()->toArray()));

        // wrong auth_id, throw exception
        $this->setExpectedException('Hook\Exceptions\NotAllowedException');
        App::collection('restricted_content')->where('auth_id', 2)->first()->toArray();
    }

    protected function setConfig($collection, $action, $config)
    {
        Config::set('security.collections.'.$collection.'.'.$action, $config);
    }

}
