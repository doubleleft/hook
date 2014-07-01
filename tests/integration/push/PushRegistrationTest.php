<?php

class PushRegistrationTest extends HTTP_TestCase
{
    public function testRegistration()
    {
        $registration = $this->post('push/registration', array(
            'app_name' => "Testing App",
            'app_version' => "1.0.0",
            'device_id' => "ios-12345",
            'platform' => "ios"
        ));
        $this->assertTrue(is_array($registration) && is_string($registration['app_name']));

        $registration = $this->post('push/registration', array(
            'app_name' => "Testing App",
            'app_version' => "1.0.0",
            'device_id' => "android-12345",
            'platform' => "android"
        ));
        $this->assertTrue(is_array($registration) && is_string($registration['app_name']));

        // $unregistration = $this->delete('push/registration', array('device_id' => "ios-12345"));
        // $this->assertTrue($unregistration['success'] === true);
    }

    public function testCreateMessage()
    {
        $message = $this->post('collection/push_messages', array('message' => "Hello!"));
    }

    public function testNotify()
    {
        $notify = $this->get('push/notify', array('X-Scheduled-Task' => 'yes'));
        $this->assertTrue($notify['push_messages'] === 1);
        $this->assertTrue($notify['devices'] >= 2);
        $this->assertTrue($notify['success'] === 0);
        $this->assertTrue($notify['failure'] === 0);

    }

}
