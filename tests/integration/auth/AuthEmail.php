<?php

class AuthEmail extends TestCase
{
    public function testInvalidAuth()
    {
        $auth = $this->post('auth/email', array('invalid_param' => 'wtf?'));
        $this->assertTrue(is_string($auth['error']), 'email and password are required.');

        $auth = $this->post('auth/email', array('email' => 'invalid email address'));
        $this->assertTrue(is_string($auth['error']), 'should output error on invalid email address.');

        $auth = $this->post('auth/email', array('email' => 'edreyer@doubleleft.com'));
        $this->assertTrue(is_string($auth['error']), 'should output error when no password given.');
    }

    public function testAuth()
    {
        $auth = $this->post('auth/email', array(
            'email' => 'edreyer' . uniqid() . '@doubleleft.com',
            'password' => '123'
        ));
        $this->assertTrue(is_array($auth) && !is_string($auth['error']));
        $this->assertTrue(is_array($auth['token']) && is_string($auth['token']['token']));

        $auth_same_address = $this->post('auth/email', array(
            'email' => $auth['email'],
            'password' => '123'
        ));
        $this->assertTrue(is_string($auth_same_address['error']));
    }

    public function testVerifyInvalid()
    {
        $auth = $this->post('auth/email/verify', array('invalid_param' => 'wtf?'));
        $this->assertTrue(is_string($auth['error']), 'email and password are required.');
        $auth = $this->post('auth/email/verify', array('email' => 'invalid email address'));
        $this->assertTrue(is_string($auth['error']), 'should output error on invalid email address.');
        $auth = $this->post('auth/email/verify', array('email' => 'edreyer@doubleleft.com'));
        $this->assertTrue(is_string($auth['error']), 'should output error when no password given.');
    }

    public function testVerify()
    {
        $auth_data = array(
            'email' => 'edreyer' . uniqid() . '@doubleleft.com',
            'password' => '12345'
        );

        // registering...
        $auth = $this->post('auth/email', $auth_data);
        $this->assertTrue(is_array($auth) && !is_string($auth['error']));

        $logged_user = $this->get('auth', array('X-Auth-Token' => $auth['token']['token']));
        $this->assertTrue($logged_user['email'] == $auth['email']);

        // verifying...
        $auth = $this->post('auth/email/verify', array(
            'email' => $auth_data['email'],
            'password' => "wrong password"
        ));
        $this->assertTrue(is_string($auth['error']), 'should output wrong password error.');

        $auth = $this->post('auth/email/verify', $auth_data);
        $this->assertTrue(is_array($auth) && !is_string($auth['error']));

        $logged_user = $this->get('auth', array('X-Auth-Token' => $auth['token']['token']));
        $this->assertTrue($logged_user['email'] == $auth['email']);
    }

    public function testForgotPassword() { }
    public function testResetPassword() { }

}
