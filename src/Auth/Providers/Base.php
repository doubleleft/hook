<?php
namespace API\Auth\Providers;

use API\Exceptions\NotImplementedException;
use API\Model\Auth as Auth;

class Base
{
    public function register($data)
    {
        throw new NotImplementedException("'register' not implemented on this provider.");
    }

    public function login($data)
    {
        throw new NotImplementedException("'login' not implemented on this provider.");
    }

    public function forgotPassword($data)
    {
        throw new NotImplementedException("'forgotPassword' not implemented on this provider.");
    }

    public function resetPassword($data)
    {
        throw new NotImplementedException("'resetPassword' not implemented on this provider.");
    }

    protected function find($key_field, $data)
    {
        return Auth::where($key_field, $data[$key_field])
            ->where('app_id', $data['app_id'])
            ->first();
    }

}
