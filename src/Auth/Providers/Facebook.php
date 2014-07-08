<?php
namespace Hook\Auth\Providers;

use Hook\Exceptions\ForbiddenException;
use Hook\Model\Auth as Auth;

class Facebook extends Base
{
    public function register($data)
    {
        $data = $this->requestFacebookGraph($data);

        $user = null;
        try {
            $user = $this->find('facebook_id', $data);
        } catch (\Illuminate\Database\QueryException $e) {}

        if (!$user) {
            $user = Auth::create($data);
        }

        return $user->dataWithToken();
    }

    public function login($data)
    {
        $userdata = null;
        if ($user = $this->find('facebook_id', $this->requestFacebookGraph($data))) {
            $userdata = $user->dataWithToken();
        }

        return $userdata;
    }

    protected function requestFacebookGraph($data)
    {
        // validate accessToken
        if (!isset($data['accessToken'])) {
            throw new ForbiddenException(__CLASS__ . ": you must provide user 'accessToken'.");
        }

        $access_token = $data['accessToken'];

        // remove invalid data from request fields
        // these fields are present on 'authResponse' from FB.login
        $invalid_keys = array('accessToken', 'expiresIn', 'signedRequest', 'userID');
        foreach ($invalid_keys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }

        $client = new \Guzzle\Http\Client("https://graph.facebook.com");
        $response = $client->get("/me?access_token={$access_token}")->send();
        $facebook_data = json_decode($response->getBody(), true);

        // Filter fields from Facebook that isn't whitelisted for auth.
        $field_whitelist = array('id', 'email', 'first_name', 'gender', 'last_name', 'link', 'locale', 'name', 'timezone', 'username');
        foreach ($facebook_data as $field => $value) {
            if (!in_array($field, $field_whitelist)) {
                unset($facebook_data[$field]);
            }
        }

        // Merge given data with facebook data
        $data = array_merge($data, $facebook_data);

        // rename 'facebook_id' field
        $data['facebook_id'] = $data['id'];
        unset($data['id']);

        return $data;
    }

}
