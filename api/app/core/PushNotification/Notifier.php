<?php
namespace PushNotification;

class Notifier {

	const MAX_RECIPIENTS_PER_REQUEST = 1000;

	// available services
	static $services = array(
		'ios' => 'PushNotification\\Services\\APNS',
		'android' => 'PushNotification\\Services\\GCM'
	);

	public function push_messages($messages) {
		$messages = $messages->get();

		// Count total devices available to deliver
		$devices = \models\App::collection('push_registrations')->
			whereIn('platform', array_keys(static::getPlatformServices()))->
			count();

		$statuses = array(
			'push_messages' => $messages->count(),
			'devices' => $devices,
			'success' => 0,
			'errors' => 0
		);

		debug("PushNotification: pushing {$statuses['push_messages']} message(s) to {$statuses['devices']} devices.");

		foreach($messages as $message) {
			$status = $this->push($message->toArray());
			$statuses['success'] += $status['success'];
			$statuses['errors'] += $status['errors'];
		}

		return $statuses;
	}

	/**
	 * push
	 * @param models\PushMessage $message
	 */
	public function push($message) {
		$status = array('success' => 0, 'errors' => 0);

		foreach(static::getPlatformServices() as $platform => $service_klass) {
			$service = new $service_klass();
			$query = \models\App::collection('push_registrations')->where('platform', $platform);
			$query->chunk(self::MAX_RECIPIENTS_PER_REQUEST, function($registrations) use (&$service, &$status, $message) {
				try {
					$chunk_status = $service->push($registrations, $message);
					$status['success'] += $chunk_status['success'];
					$status['errors'] += $chunk_status['errors'];
				} catch (\Exception $e) {
					debug("PushNotification: platform: {$platform} -> {$e->getMessage()}");
				}
			});
		}

		return $status;
	}

	/**
	 * getPlatformServices
	 * @static
	 */
	public static function getPlatformServices() {
		return self::$services;
	}

}
