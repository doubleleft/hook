<?php

class AuthFacebook extends HTTP_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->delete('collection/auth');
    }

    public function testInvalidAuth()
    {
        $auth = $this->post('auth/facebook', array('invalid_param' => 'wtf?'));
        $this->assertTrue(is_string($auth['error']), 'access token is required');

        $auth = $this->post('auth/facebook', array('email' => 'edreyer@doubleleft.com'));
        $this->assertTrue(is_string($auth['error']), 'access token is required');

        $auth = $this->post('auth/facebook', array('accessToken' => 'some-invalid-token'));
        $this->assertTrue(is_string($auth['error']), 'invalid access token');
    }

    public function testValidAuth()
    {
        // $auth = $this->post('auth/facebook', array('accessToken' => 'CAAHYb81YRgUBAB7pWfG0BNXxvYAiZCtwMgdjfBFxJP94iQwSpoNId6KuyP7K3XXzVMDbqB5Sa4xLdi3z8SZCiZB1mKleos5jc1oFTVZC6EYZAOLL4w3VDbkgIhCCnEUgrpr0B0pbyk4Unl5kUrZAn71MNHkocNXdkpBAQ8bmgdomyoZBiZBjZAHSZASO9csldMRuIZD'));
        // $this->assertTrue(is_array($auth) && !is_string($auth['error']));
        // $this->assertTrue(is_array($auth['token']) && is_string($auth['token']['token']));
    }

    public function testAuthWithAdditionalFields()
    {
        // $auth = $this->post('auth/facebook', array(
        // 	'accessToken' => 'CAAHYb81YRgUBAB7pWfG0BNXxvYAiZCtwMgdjfBFxJP94iQwSpoNId6KuyP7K3XXzVMDbqB5Sa4xLdi3z8SZCiZB1mKleos5jc1oFTVZC6EYZAOLL4w3VDbkgIhCCnEUgrpr0B0pbyk4Unl5kUrZAn71MNHkocNXdkpBAQ8bmgdomyoZBiZBjZAHSZASO9csldMRuIZD',
        // 	'additional_field' => "fb auth with additional fields!"
        // ));
        // $this->assertTrue(is_array($auth) && !is_string($auth['error']), "shouldn't throw error posting additional data");
        // $this->assertTrue(is_array($auth['token']) && is_string($auth['token']['token']), "should return client auth token");
        // $this->assertTrue(isset($auth['additional_field']) && $auth['additional_field'] == "fb auth with additional fields!", "should persist additional field data");
    }

}
