<?php namespace Hook\View;

use LightnCandy;

class Template {

    protected $filename;
    protected $code;
    protected $is_precompiled;
    protected $updated_at;

    public function __construct($data)
    {
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = time();
        }

        // create views directory if it doesn't exists.
        $directory = storage_dir() . 'views';
        if (!is_dir($directory)) { mkdir($directory); }


        $this->filename =  $directory . '/' . $data['filename'] . '.php';
        $this->updated_at = $data['updated_at'];

        $this->is_precompiled = (file_exists($this->filename) &&
            abs($this->updated_at - filemtime($this->filename)) < 10);

        if (!$this->is_precompiled) {
            $this->code = $data['code'];
        }
    }

    public function render($data) {
        // compile template to file if it's not compiled
        if (!$this->is_precompiled) { $this->compile(); }

        // require template renderer and render it.
        $renderer = include($this->filename);
        return $renderer($data);
    }

    protected function compile() {
        file_put_contents($this->filename, LightnCandy::compile($this->code));
        touch($this->filename, $this->updated_at, $this->updated_at);
        return true;
    }

}
