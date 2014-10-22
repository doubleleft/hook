<?php namespace Hook\Controllers;

use Hook\Application\Context;
use Hook\Exceptions\UnauthorizedException;

use Hook\Application\Config;
use Hook\Model\Auth;
use Hook\Model\AuthIdentity;

use Hook\Http\Response;

use Opauth;

class OAuthController extends HookController {

    public function relay_frame() {
        Response::header('Content-type', 'text/html');
        Response::setBody('<!DOCTYPE html>
            <html>
            <script>
              function doPost(msg, origin) {
                window.parent.postMessage(msg, origin);
              }
            </script>
           </html>
        ');
    }

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

                // Use mozilla/winchan to allow trusted cross-browser postMessages
                $winchanjs = 'WinChan=function(){var RELAY_FRAME_NAME="__winchan_relay_frame";var CLOSE_CMD="die";function addListener(w,event,cb){if(w.attachEvent)w.attachEvent("on"+event,cb);else if(w.addEventListener)w.addEventListener(event,cb,false)}function removeListener(w,event,cb){if(w.detachEvent)w.detachEvent("on"+event,cb);else if(w.removeEventListener)w.removeEventListener(event,cb,false)}function isInternetExplorer(){var rv=-1;var ua=navigator.userAgent;if(navigator.appName==="Microsoft Internet Explorer"){var re=new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");if(re.exec(ua)!=null)rv=parseFloat(RegExp.$1)}else if(ua.indexOf("Trident")>-1){var re=new RegExp("rv:([0-9]{2,2}[.0-9]{0,})");if(re.exec(ua)!==null){rv=parseFloat(RegExp.$1)}}return rv>=8}function isFennec(){try{var userAgent=navigator.userAgent;return userAgent.indexOf("Fennec/")!=-1||userAgent.indexOf("Firefox/")!=-1&&userAgent.indexOf("Android")!=-1}catch(e){}return false}function isSupported(){return window.JSON&&window.JSON.stringify&&window.JSON.parse&&window.postMessage}function extractOrigin(url){if(!/^https?:\/\//.test(url))url=window.location.href;var a=document.createElement("a");a.href=url;return a.protocol+"//"+a.host}function findRelay(){var loc=window.location;var frames=window.opener.frames;for(var i=frames.length-1;i>=0;i--){try{if(frames[i].location.protocol===window.location.protocol&&frames[i].location.host===window.location.host&&frames[i].name===RELAY_FRAME_NAME){return frames[i]}}catch(e){}}return}var isIE=isInternetExplorer();if(isSupported()){return{open:function(opts,cb){if(!cb)throw"missing required callback argument";var err;if(!opts.url)err="missing required \'url\' parameter";if(!opts.relay_url)err="missing required \'relay_url\' parameter";if(err)setTimeout(function(){cb(err)},0);if(!opts.window_name)opts.window_name=null;if(!opts.window_features||isFennec())opts.window_features=undefined;var iframe;var origin=extractOrigin(opts.url);if(origin!==extractOrigin(opts.relay_url)){return setTimeout(function(){cb("invalid arguments: origin of url and relay_url must match")},0)}var messageTarget;if(isIE){iframe=document.createElement("iframe");iframe.setAttribute("src",opts.relay_url);iframe.style.display="none";iframe.setAttribute("name",RELAY_FRAME_NAME);document.body.appendChild(iframe);messageTarget=iframe.contentWindow}var w=window.open(opts.url,opts.window_name,opts.window_features);if(!messageTarget)messageTarget=w;var closeInterval=setInterval(function(){if(w&&w.closed){cleanup();if(cb){cb("unknown closed window");cb=null}}},500);var req=JSON.stringify({a:"request",d:opts.params});function cleanup(){if(iframe)document.body.removeChild(iframe);iframe=undefined;if(closeInterval)closeInterval=clearInterval(closeInterval);removeListener(window,"message",onMessage);removeListener(window,"unload",cleanup);if(w){try{w.close()}catch(securityViolation){messageTarget.postMessage(CLOSE_CMD,origin)}}w=messageTarget=undefined}addListener(window,"unload",cleanup);function onMessage(e){if(e.origin!==origin){return}try{var d=JSON.parse(e.data);if(d.a==="ready")messageTarget.postMessage(req,origin);else if(d.a==="error"){cleanup();if(cb){cb(d.d);cb=null}}else if(d.a==="response"){cleanup();if(cb){cb(null,d.d);cb=null}}}catch(err){}}addListener(window,"message",onMessage);return{close:cleanup,focus:function(){if(w){try{w.focus()}catch(e){}}}}},onOpen:function(cb){var o="*";var msgTarget=isIE?findRelay():window.opener;if(!msgTarget)throw"cant find relay frame";function doPost(msg){msg=JSON.stringify(msg);if(isIE)msgTarget.doPost(msg,o);else msgTarget.postMessage(msg,o)}function onMessage(e){var d;try{d=JSON.parse(e.data)}catch(err){}if(!d||d.a!=="request")return;removeListener(window,"message",onMessage);o=e.origin;if(cb){setTimeout(function(){cb(o,d.d,function(r){cb=undefined;doPost({a:"response",d:r})})},0)}}function onDie(e){if(e.data===CLOSE_CMD){try{window.close()}catch(o_O){}}}addListener(isIE?msgTarget:window,"message",onMessage);addListener(isIE?msgTarget:window,"message",onDie);try{doPost({a:"ready"})}catch(e){addListener(msgTarget,"load",function(e){doPost({a:"ready"})})}var onUnload=function(){try{removeListener(isIE?msgTarget:window,"message",onDie)}catch(ohWell){}if(cb)doPost({a:"error",d:"client closed window"});cb=undefined;try{window.close()}catch(e){}};addListener(window,"unload",onUnload);return{detach:function(){removeListener(window,"unload",onUnload)}}}}}else{return{open:function(url,winopts,arg,cb){setTimeout(function(){cb("unsupported browser")},0)},onOpen:function(cb){setTimeout(function(){cb("unsupported browser")},0)}}}}();';

                Response::setBody("
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <meta http-equiv='X-UA-Compatible' content='chrome=1' />
                        </head>
                        <body>
                        <script type='text/javascript'>
                          {$winchanjs}
                          WinChan.onOpen(function(origin, args, cb) {
                            cb(".to_json($data).");
                          });
                        </script>
                        </body>
                    </html>
                ");
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
