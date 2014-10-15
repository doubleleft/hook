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
        $filename = md5($file['name']) . uniqid() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $public_url = $this->store($filename, file_get_contents($file['tmp_name']), $options);
        unlink($file['tmp_name']);

        return $public_url;
    }

}
