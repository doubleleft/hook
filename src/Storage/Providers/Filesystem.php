<?php
namespace Hook\Storage\Providers;

use Hook\Model\App as App;

class Filesystem extends Base
{

    public function store($filename, $data, $options = array())
    {
        $filename = md5($filename) . uniqid() . "." . pathinfo($filename, PATHINFO_EXTENSION);
        $public_dir = storage_dir(false);

        $dir = __DIR__ . '/../../../' . $public_dir . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_put_contents($dir . $filename, $data)) {
            return public_url($public_dir . $filename);
        }
    }

}
