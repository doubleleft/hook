<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Cache;
use Hook\Database\Schema;

use Hook\Http\Input;
use Hook\Http\Request;

use Hook\Package;
use Hook\Application\Context;
use Hook\Application\Config;
use Hook\Exceptions\UnauthorizedException;
use Hook\Exceptions\NotAllowedException;

use Carbon\Carbon;

class ApplicationController extends HookController {

    public static function isRootOperation() {
        return (preg_match('/^\/(apps)$/', Request::path()) && static::isAllowedIP());
    }

    public function before() {
        $is_commandline = true; //(Request::header('User-Agent') == 'hook-cli');

        $key = Context::getKey();
        $allowed = $is_commandline && ($key && $key->isCommandline());

        if (!static::isRootOperation() && !$allowed) {
            throw new UnauthorizedException("Your IP Address is not allowed to perform this operation.");
        }
    }

    public function index() {
        Context::setTablePrefix('');
        return Model\App::all();
    }

    public function create() {
        // Reset table prefix
        Context::setTablePrefix('');

        $data = Model\App::create(Input::get('app'));
        $response = $data->toArray();

        // Set application prefix for migration
        Context::setKey($data->keys[0]);
        Context::migrate();

        return $response;
    }

    public function delete_cache() {
        Cache\Cache::flush();
        return array('success' => true);
    }

    public function logs() {
        $file_path = storage_dir() . 'logs.txt';

        if (!file_exists($file_path)) {
            throw new NotAllowedException("Logs not allowed in this server.");
        }

        $is_tail = (Input::get('tail')) ? '-f ' : '';
        $lines = Input::get('n', 40);

        $handle = popen("tail -n {$lines} {$is_tail} {$file_path} 2>&1", 'r');
        $content = "";
        while (!feof($handle)) {
            $content .= fgets($handle);
            ob_flush();
            flush();
            usleep(300);
        }
        pclose($handle);
        return array('text' => $content);
    }

    public function tasks() {
        return Model\ScheduledTask::all()->toArray();
    }

    public function recreate_tasks() {
        // Remove all scheduled tasks for this app
        return array(
            'success' => Model\ScheduledTask::deploy(Input::get('schedule', array()))
        );
    }

    public function dump_deploy() {
        return array(
            'modules' => Model\Module::dump(),
            'packages' => Package\Manager::dump()
        );
    }

    public function deploy() {
        set_time_limit(0);
        $statuses = array();

        // application configs
        $configs = Input::get('config', array());
        $configs['security'] = Input::get('security', array());

        // Flush cache on deployment
        Cache\Cache::flush();

        // Migrate and keep schema cache
        $collections_migrated = 0;
        foreach(Input::get('schema', array()) as $collection => $config) {
            if (Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config)) {
                $collections_migrated += 1;
            }
        }
        $statuses['schema'] = $collections_migrated;

        // do we have write permission on this server?
        if (is_writable(storage_dir())) {
            $statuses['config'] = Config::deploy($configs);
            $statuses['schedule'] = Model\ScheduledTask::deploy(Input::get('schedule', array()));

            // install composer packages
            $statuses['packages'] = Package\Manager::install(Input::get('packages', array()));
        } else {
            $statuses['config'] = $without_write_permission_message;
            $statuses['schedule'] = $without_write_permission_message;
            $statuses['packages'] = $without_write_permission_message;
        }

        // modules
        $statuses['modules'] = Model\Module::deploy(Input::get('modules', array()));

        return $statuses;
    }

    public function configs() {
        return Model\AppConfig::all();
    }

    public function modules() {
        return Model\Module::all();
    }

    public function schema() {
        return Schema\Builder::dump();
    }

    public function upload_schema() {
        $schema = Input::get();

        foreach($schema as $collection => $config) {
            Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config);
        }

        return array('success' => true);
    }

    public function delete() {
        return array('success' => false);
    }


    public static function isAllowedIP() {
        $allowed = false;
        $allowed_ip_addresses = Context::config('allowed_ip_addresses');

        if ($allowed_ip_addresses && !empty($allowed_ip_addresses)) {
            $allowed = in_array("*", $allowed_ip_addresses) ||
                in_array(Request::ip(), $allowed_ip_addresses);
        }

        return $allowed;
    }

}
