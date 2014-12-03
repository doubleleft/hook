<?php
namespace Hook\Storage\Providers;

use Hook\Exceptions\NotImplementedException;

class Base
{

    public function store($filename, $data, $options = array())
    {
        throw new NotImplementedException("'store' not implemented on this provider.");
    }

    public function read($file)
    {
        throw new NotImplementedException("'read' not implemented on this provider.");
    }

    public function realpath($file)
    {
        throw new NotImplementedException("'realpath' not implemented on this provider.");
    }

}
