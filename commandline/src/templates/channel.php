<?php

/**
 * Custom channel: {channel}
 */

class {name} implements Ratchet\Wamp\WampServerInterface {

	public function onPublish(Ratchet\ConnectionInterface $conn, $topic, $message, array $exclude, array $eligible) {
		// // Broadcast message to all subscribers
		// $topic->broadcast($message);

		// Filter excluded/eligible clients
		foreach($topic->getIterator() as $conn) {
			$is_excluded = !in_array($conn->WAMP->sessionId, $exclude);
			$is_eligible = count($eligible) === 0 || in_array($conn->WAMP->sessionId, $eligible);
			if ($is_excluded && $is_eligible) {
				$conn->event($topic, $message);
			}
		}

	}

	public function onSubscribe(Ratchet\ConnectionInterface $conn, $topic) {
	}

	public function onUnSubscribe(Ratchet\ConnectionInterface $conn, $topic) {
	}

	public function onOpen(Ratchet\ConnectionInterface $conn) {
	}

	public function onClose(Ratchet\ConnectionInterface $conn) {
	}

	public function onCall(Ratchet\ConnectionInterface $conn, $id, $topic, array $params) {
	}

	public function onError(Ratchet\ConnectionInterface $conn, \Exception $e) {
	}

}
