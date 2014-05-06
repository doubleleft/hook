<?php

class LogWriter {
	protected $file_path;

	function __construct($file) {
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
