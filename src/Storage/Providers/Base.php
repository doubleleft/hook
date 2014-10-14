<?php
namespace Hook\Storage\Providers;

use Hook\Exceptions\NotImplementedException;

class Base
{

    public function store($filename, $data, $options = array())
    {
        throw new NotImplementedException("'store' not implemented on this provider.");
    }

    public function upload($file, $options = array())
    {
        $public_url = $this->store($file['name'], file_get_contents($file['tmp_name']), $options);

        unlink($file['tmp_name']);

        return $public_url;
    }

}
