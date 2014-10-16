<?php
namespace Hook\Storage\Providers;

use Hook\Model\App as App;

class Filesystem extends Base
{

    public function store($filename, $data, $options = array())
    {
        $storage_dir = storage_dir(true);
        $public_dir = storage_dir(false);

        // create directory if it doesn't exists
        if (!is_dir($storage_dir)) { mkdir($storage_dir, 0777, true); }

        if (file_put_contents($storage_dir . $filename, $data)) {
            return public_url($public_dir . $filename);
        }
    }

}
