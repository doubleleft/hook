<?php

/*
 * Extending model funcionality
 * ----------------------------
 *
 * <?php
 * User::observe(new Users);
 *
 * --------------------------------
 *
 * require.json
 * {
 *   "swiftmailer/swiftmailer" : "*"
 * }
 */

class UserObserver {

	public function creating() {
		var_dump("creating...");
	}

	public function created() {
		var_dump("created...");
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

Models\Collection::observe(new UserObserver);
