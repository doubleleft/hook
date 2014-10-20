<?php namespace Hook\Controllers;

use Hook\Application\Context;
use Hook\Exceptions\UnauthorizedException;

use Hook\Application\Config;
use Hook\Model\Auth;
use Hook\Model\AuthIdentity;

use Hook\Http\Response;

use Opauth;

class OAuthController extends HookController {

    public function auth($strategy=null, $callback=null) {
        $query_params = $this->getQueryParams();

        if (isset($_POST['opauth'])) {
            $opauth = unserialize(base64_decode($_POST['opauth']));

            if (isset($opauth['error'])) {
                throw new UnauthorizedException($opauth['error']['raw']);
            }

            $auth = $opauth['auth'];

            $identity = AuthIdentity::firstOrNew(array(
                'provider' => strtolower($auth['provider']),
                'uid' => $auth['uid'],
            ));

            if (!$identity->auth_id) {
                // cleanup nested infos before registering it
                foreach($auth['info'] as $key => $value) {
                    if (is_array($value)) {
                        unset($auth['info'][$key]);
                    }
                }

                // register new auth
                $auth = Auth::create($auth['info']);
                $identity->auth_id = $auth->_id;
                $identity->save();
            } else {
                $auth = $identity->auth;
            }

            $data = $auth->dataWithToken();

            if (Context::getKey()->isBrowser()) {
                Response::header('Content-type', 'text/html');
                $js_origin = "window.opener.location.protocol + '//' + window.opener.location.hostname + (window.opener.location.port ? ':' + window.opener.location.port: '')";
                Response::setBody("<script>window.opener.postMessage(".to_json($data).", {$js_origin});</script>");

            } else {
                $this->json($data);
            }

            return true;
        }

        $opauth = new Opauth(array(
            'path' => substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'oauth/') + 6),
            // 'callback_url' => '{path}callback',
            'callback_url' => '{path}callback' . $query_params,
            'callback_transport' => 'post',
            'Strategy' => Config::get('oauth'),
            'security_salt' => Context::getKey()->app->secret,
            // 'debug' => true,
        ), false);

        $this->fixOauthStrategiesCallback($opauth, $query_params);

        $opauth->run();
    }

    protected function fixOauthStrategiesCallback($opauth, $query_params) {
        // append query_params to every strategy callback
        foreach($opauth->env['Strategy'] as $name => $configs) {
            $opauth->env['Strategy'][$name]['redirect_uri'] = '{complete_url_to_strategy}int_callback' . $query_params;
            $opauth->env['Strategy'][$name]['oauth_callback'] = '{complete_url_to_strategy}oauth_callback' . $query_params;
        }
    }

    protected function getQueryParams() {
        $keep_query_keys = array_filter(array('X-App-Id', 'X-App-Key'), function($param) {
            return isset($_GET[$param]);
        });
        $keep_query_values = array_map(function($param) { return $_GET[$param]; }, $keep_query_keys);
        return '?' . http_build_query(array_combine($keep_query_keys, $keep_query_values));
    }

}
