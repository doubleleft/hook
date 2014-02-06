<?php

/**
 * Custom observer for: users
 */

class Users {

	public function creating($data) {
		// before create

		if ($data->age < 12) {
			Mail::send(array(
				'to' => $data->email,
				'template' => 'under-12.html',
				'data' => $data->toArray()
			));
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
