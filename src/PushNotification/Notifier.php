<?php
namespace Hook\PushNotification;

use Hook\Logger\Logger;
use Hook\Model\PushMessage as PushMessage;
use Hook\Model\App as App;

class Notifier
{
    // GCM currently supports 1000 registration_ids per request.
    const MAX_RECIPIENTS_PER_REQUEST = 1000;

    // available services
    static $services = array(
        'ios' => 'Hook\\PushNotification\\Services\\APNS',
        'android' => 'Hook\\PushNotification\\Services\\GCM'
    );

    public function push_messages($query)
    {
        $messages = $query->get();

        //lock messages
        $query->update(array("status" => PushMessage::STATUS_SENDING));

        // Count total devices available to deliver
        $devices = App::collection('push_registrations')->
            whereIn('platform', array_keys(static::getPlatformServices()))->
            count();

        $statuses = array(
            'push_messages' => $messages->count(),
            'devices' => $devices,
            'success' => 0,
            'failure' => 0
        );

        Logger::debug("PushNotification: pushing {$statuses['push_messages']} message(s) to {$statuses['devices']} devices.");

        foreach ($messages as $message) {
            $status = $this->push($message->toArray());
            $statuses['success'] += $status['success'];
            $statuses['failure'] += $status['failure'];
            $message->update(array(
                'devices' => $statuses['devices'],
                'failure' => $status['failure'],
                'status' => PushMessage::STATUS_SENT
            ));
        }

        return $statuses;
    }

    /**
     * push
     * @param Hook\Model\PushMessage $message
     */
    public function push($message)
    {
        $status = array('success' => 0, 'failure' => 0);

        foreach (static::getPlatformServices() as $platform => $service_klass) {
            $service = new $service_klass();
            $query = App::collection('push_registrations')->where('platform', $platform);
            $query->chunk(self::MAX_RECIPIENTS_PER_REQUEST, function ($registrations) use (&$platform, &$service, &$status, $message) {
                try {
                    $chunk_status = $service->push($registrations, $message);
                    $status['success'] += $chunk_status['success'];
                    $status['failure'] += $chunk_status['failure'];
                } catch (\Exception $e) {
                    Logger::debug("PushNotification: platform: {$platform} -> {$e->getMessage()}");
                }
            });
        }

        return $status;
    }

    /**
     * getPlatformServices
     * @static
     */
    public static function getPlatformServices()
    {
        return self::$services;
    }

}
