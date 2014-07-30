<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Http\Input;
use Hook\Http\Request;

class ChannelController extends HookController {

    public function index($name) {
        $name = implode("/", $name);
        return $this->json(Model\App::collection('channel_messages')->filter(Input::get('q'))
            ->where('channel', $name)
            ->get());
    }

    public function store($name) {
        $name = implode("/", $name);
        return $this->json( Model\ChannelMessage::create(array_merge(Input::get(), array(
            'channel' => $name
        ))));
    }

}

