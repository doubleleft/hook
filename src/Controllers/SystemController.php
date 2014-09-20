<?php namespace Hook\Controllers;

use Hook\Http\Request;

class SystemController extends HookController {

    public function time() {
        return $this->json( time() );
    }

    public function ip() {
        $ip = Request::ip();
        return $this->json( json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"), true) );
    }

}
