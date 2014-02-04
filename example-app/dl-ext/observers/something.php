<?php

/**
 * Custom observer for: something
 */

class Something {

	public function creating($data) {
		// before create

		if ($data->some_data == "invalid") {
		  return false;
		}
	}

	public function created($data) {
		// after create
		// Utils::send_mail();
	}

	public function updating($data) {
		// before update
	}

	public function updated($data) {
		// after update
	}

	public function saving($data) {
		// before save
	}

	public function saved($data) {
		// after save
	}

	public function deleting($data) {
		// before delete
	}

	public function deleted($data) {
		// after delete
	}

	public function restoring($data) {
		// before restore (soft-delete)
	}

	public function restored($data) {
		// after restore
	}

}
