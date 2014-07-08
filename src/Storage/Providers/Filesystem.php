<?php
namespace Hook\Storage\Providers;

use Hook\Model\App as App;

class Filesystem extends Base
{
    public function store($filename, $data)
    {
        $filename = md5($filename) . uniqid() . "." . pathinfo($filename, PATHINFO_EXTENSION);
        $public_dir = 'storage/files/' . App::currentId() . '/';

        $dir = __DIR__ . '/../../../' . $public_dir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_put_contents($dir . $filename, $data)) {
            return public_url($public_dir . $filename);
        }
    }

    public function upload($file, $options=array())
    {
        $filename = md5($file['name']) . uniqid() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $dir = $this->_uploadDir();
        $public_dir = $this->_publicDir();
        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            return public_url($public_dir . $filename);
        }
    }

    public function _publicDir()
    {
        $public_dir = 'storage/files/' . App::currentId() . '/';

        return $public_dir;
    }

    public function _uploadDir()
    {
        $dir = __DIR__ . '/../../../' . $this->_publicDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

}
