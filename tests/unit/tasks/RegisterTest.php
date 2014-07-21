<?php

use Hook\Database\AppContext as AppContext;
use Hook\Model\AppKey as AppKey;
use Hook\Model\ScheduledTask as ScheduledTask;

class RegisterTest extends TestCase
{

    public function testRegister()
    {
        $task = new ScheduledTask(array(
            'task' => "something",
            'schedule' => "daily"
        ));
        preg_match("/X-App-Key: ([^']+)/", $task->getCommand(), $matches);
        $this->assertTrue(strlen($matches[1]) == 32, "should find a valid 32-char key.");
        $this->assertEquals($matches[1], AppContext::getAppKeys(AppKey::TYPE_SERVER)->first()->key, "tasks should use a valid server key");
    }

}
