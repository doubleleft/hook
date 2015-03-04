<?php

class TrustedActionTest extends TestCase {

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

    // TODO: write test cases for Auth::$_isTrustedAction

}
