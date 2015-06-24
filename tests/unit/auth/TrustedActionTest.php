<?php
use Hook\Application\Context;

use Hook\Model\Collection;
use Hook\Model\Auth;
use Hook\Model\AuthToken;
use Hook\Model\AppKey;

class TrustedActionTest extends TestCase {

    public function setUp()
    {
        // TODO: need to figure out a way to remove this.
        Collection::from('auths');

        // only browser/server/device keys are affected by Role system.
        AppKey::current()->type = AppKey::TYPE_BROWSER;
        Context::setTrusted(false);
    }

    public function tearDown()
    {
        // restore commandline key
        AppKey::current()->type = AppKey::TYPE_CLI;

        Context::setTrusted(false);

        // reset active auth token
        AuthToken::setCurrent(null);
    }

    /**
     * @expectedException Hook\Exceptions\ForbiddenException
     * @expectedExceptionMessage not_allowed
     */
    public function testCannotCreateAuth() {
        $auth = new Auth(array(
            'email' => "user@doubleleft.com",
            'password' => 'teste',
            'name' => "User"
        ));
        $auth->save();
    }

    public function testCreateAuth() {
        $auth = new Auth(array(
            'email' => "user@doubleleft.com",
            'password' => 'teste',
            'name' => "User"
        ));
        $auth->setTrustedAction(true);
        $auth->save();
    }

    public function testCreateAuthWithRole() {
        $auth = new Auth(array(
            'email' => "user@doubleleft.com",
            'password' => 'teste',
            'name' => "User",
            'role' => "admin"
        ));
        $auth->setTrustedAction(true);
        $auth->save();
        $this->assertTrue($auth->role == null);

        Context::setTrusted(true);
        $auth = new Auth(array(
            'email' => "admin@doubleleft.com",
            'password' => 'teste',
            'name' => "User",
            'role' => "admin"
        ));
        $auth->setTrustedAction(true);
        $auth->save();
        $this->assertTrue($auth->role == "admin");
    }

    /**
     * @expectedException Hook\Exceptions\ForbiddenException
     * @expectedExceptionMessage not_allowed
     */
    public function testUntrustedCantUpdate() {
        $auth = Auth::where('email', "user@doubleleft.com")->first();
        $auth->name = "something";
        $auth->save();
    }

    public function testTrustedCanUpdate() {
        $auth = Auth::where('email', "user@doubleleft.com")->first();
        $auth->name = "something";
        $auth->setTrustedAction(true);
        $auth->save();
        $this->assertTrue($auth->name == "something");
    }

    public function testTrustedCantUpdateRole() {
        $auth = Auth::where('email', "user@doubleleft.com")->first();
        $auth->role = "admin";
        $auth->setTrustedAction(true);
        $auth->save();
        $this->assertTrue($auth->role == null);
    }

    public function testTrustedCanUpdateRole() {
        Context::setTrusted(true);
        $auth = Auth::where('email', "user@doubleleft.com")->first();
        $auth->role = "admin";
        $auth->setTrustedAction(true);
        $auth->save();
        $this->assertTrue($auth->role == "admin");
    }


}
