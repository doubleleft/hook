<?php

/**
 * Custom observer for: {collection}
 */

class {name} {

	// customize response here
	public function toArray($model, $array) {
		return $array;
	}

	// before create
	public function creating($model) {
	}

	// after create
	public function created($model) {
	}

	// before update
	public function updating($model) {
	}

	// after update
	public function updated($model) {
	}

	// before save
	public function saving($model) {
	}

	// after save
	public function saved($model) {
	}

	// before delete
	public function deleting($model) {
	}

	// after delete
	public function deleted($model) {
	}

	// before update multiple rows
	public function updating_multiple($query, $values) {
		return $values;
	}

	// before delete multiple rows
	public function deleting_multiple($query) {
	}

}
