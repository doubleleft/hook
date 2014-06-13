<?php

class AuthFacebook extends TestCase {

	public function testInvalidAuth() {
		$auth = $this->post('auth/facebook', array('invalid_param' => 'wtf?'));
		$this->assertTrue(is_string($auth['error']), 'email and password are required.');

		$auth = $this->post('auth/facebook', array('accessToken' => 'some-invalid-token'));
		$this->assertTrue(is_string($auth['error']), 'invalid access token');

		// $auth = $this->post('auth/facebook', array('email' => 'edreyer@doubleleft.com'));
		// $this->assertTrue(is_string($auth['error']), 'should output error when no password given.');
	}

	public function testValidAuth() {
		$auth = $this->post('auth/facebook', array('accessToken' => 'CAAToRUqZAiGcBACPfYzMMcaXZA9o1zmh7AO1wVhBMC0CUR6sKz7WfHcXnLcVjVpiBDOyt9d8YpPZCX4MhuaTuaRMjwbGvyUR1Bfh2IQkN4uftPurRcBiEShOcmq5s7eCanCQjNNWSHZCa7L30IEbHGhrdeAt7sNSmGKkI1ZBT46N931jl9USVWz7QyYoDHRgZD'));
		$this->assertTrue(is_array($auth) && !is_string($auth['error']));
		$this->assertTrue(is_array($auth['token']) && is_string($auth['token']['token']));

		$auth = $this->post('auth/facebook', array(
			'accessToken' => 'CAACEdEose0cBAMVvqT1nbHwNhWwxiCZADvwnRyHOYoXDurXLs9kRMcV92JpJJLed9PDwsAZAyTgALBsYRshLZB9vXg0KF3ZBseAfWFfeyqB1ynHY1JXPKUP4bujHLZAG5tSD9I5dmtrH5SI4DJLRZC0x0LM0ZCgfuOBmXkoSrZBGWtMEbkh9YsKrYCGMZD',
			'additional_field' => "fb auth with additional fields!"
		));
		$this->assertTrue(is_array($auth) && !is_string($auth['error']), "shouldn't throw error posting additional data");
		$this->assertTrue(is_array($auth['token']) && is_string($auth['token']['token']), "should return client auth token");
		$this->assertTrue(isset($auth['additional_field']) && $auth['additional_field'] == "fb auth with additional fields!", "should persist additional field data");
	}

}

