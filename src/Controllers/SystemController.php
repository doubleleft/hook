<?php namespace Hook\Controllers;

class SystemController extends HookController {

    public function time() {
        return time();
    }

    public function ip() {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_REAL_IP"])) {
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        }
        return Response::json( json_decode(file_get_contents("http://ipinfo.io/$ip/json"), true) );
    }

}
