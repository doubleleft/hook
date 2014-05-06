<?php

class LogWriter {
	protected $file_path;

	function __construct($file) {
		// create log directory if it doesn't exists.
		if (!file_exists($file)) {
			mkdir(dirname($file), 0777, true);
		}
		$this->file_path = $file;
	}

	public function write($message) {
		$fp = fopen($this->file_path, 'a+');
		fwrite($fp, $message);
		fclose($fp);
	}

	/**
	 * getFilePath
	 * @return string
	 */
	public function getFilePath() {
		return $this->file_path;
	}

}
