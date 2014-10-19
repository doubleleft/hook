<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Http\Input;
use Hook\Http\Request;

use Hook\Application\Context;
use Hook\Exceptions\ForbiddenException;

use Hook\PushNotification;

class PushNotificationController extends HookController {

    public function store() {
        if (!Context::getKey()->isDevice()) {
            throw new ForbiddenException("Need a 'device' key to perform this action.");
        }

        $data = Input::get('d', Input::get('data', Input::get()));
        return $this->json(Model\PushRegistration::create($data));
    }

    public function delete() {
        if (!Context::getKey()->isDevice()) {
            throw new ForbiddenException("Need a 'device' key to perform this action.");
        }

        $data = Input::get('d', Input::get('data', Input::get()));
        if (!isset($data['device_id'])) {
            throw new \Exception("'device_id' is required to delete push registration.");
        }
        $registration = Model\PushRegistration::where('device_id', $data['device_id']);
        return $this->json(array('success' => ($registration->delete() == 1)));
    }

    public function notify() {
        if (!(Context::getKey()->isServer() && Request::header('X-Scheduled-Task'))) {
            throw new ForbiddenException("Need a 'device' key to perform this action.");
        }

        $notifier = new PushNotification\Notifier();
        $messages = Model\App::collection('push_messages')->where('status', Model\PushMessage::STATUS_QUEUE);
        return $this->json($notifier->push_messages($messages));
    }

}
