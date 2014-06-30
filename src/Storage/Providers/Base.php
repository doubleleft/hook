<?php
namespace API\Storage\Providers;

class Base
{
    public function upload($file, $options=array())
    {
        throw new \Exception("'upload' not implemented on this provider.");
    }

}
