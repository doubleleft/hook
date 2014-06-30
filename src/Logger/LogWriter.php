<?php
namespace API\Logger;

class LogWriter
{
    protected $file_path;

    public function __construct($file)
    {
        // create log directory if it doesn't exists.
        $log_dir = dirname($file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        $this->file_path = $file;
    }

    public function write($message)
    {
        $fp = fopen($this->file_path, 'a+');
        fwrite($fp, $message . PHP_EOL);
        fclose($fp);
    }

    /**
     * getFilePath
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

}
