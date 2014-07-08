<?php
namespace Hook\Storage\Providers;

class Dropbox extends Base
{
    public function upload($file, $options=array())
    {
        return false;
    }

}
