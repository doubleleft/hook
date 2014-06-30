<?php
namespace API\Auth\Providers;

use API\Exceptions\NotImplementedException;

class Base
{
    public function authenticate($data)
    {
        throw new NotImplementedException("'authenticate' not implemented on this provider.");
    }

    public function verify($data)
    {
        throw new NotImplementedException("'verify' not implemented on this provider.");
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
        return \Model\Auth::where($key_field, $data[$key_field])
            ->where('app_id', $data['app_id'])
            ->first();
    }

}
