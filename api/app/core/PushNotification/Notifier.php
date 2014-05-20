<?php
namespace PushNotification;

class Notifier {

	// available services
	static $services = array(
		'ios' => 'PushNotification\\Services\\APNS',
		'android' => 'PushNotification\\Services\\GCM'
	);

	/**
	 * push
	 * @param models\PushMessage $message
	 */
	public function push($message) {
		var_dump(static::getPlatformServices());

		foreach(static::getPlatformServices() as $platform => $service_klass) {
			$service = new $service_klass();
			$query = \models\App::collection('push_registrations')->where('platform', $platform);
			$query->chunk(200, function($registrations) use (&$service, $message) {
				$service->push($registrations, $message);
			});
		}
	}

	/**
	 * getPlatformServices
	 * @static
	 */
	public static function getPlatformServices() {
		return self::$services;
	}

}
