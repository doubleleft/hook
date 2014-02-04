<?php

class Users {

	public function creating() {
		file_put_contents('php://stderr', 'creating...');
	}

	public function created() {
		file_put_contents('php://stderr', 'created...');
	}

	public function updating() {
	}

	public function updated() {
	}

	public function saving() {
	}

	public function saved() {
	}

	public function deleting() {
	}

	public function deleted() {
	}

	public function restoring() {
	}

	public function restored() {
	}

}
