<?php namespace Hook\Controllers;

use Hook\Model;
use Hook\Cache;
use Hook\Database\Schema;

use Hook\Http\Input;
use Hook\Http\Request;

use Hook\Package;
use Hook\Database\AppContext;
use Hook\Exceptions\UnauthorizedException;

use Carbon\Carbon;

class AppsController extends HookController {

    public static function isRootOperation() {
        return (preg_match('/^\/(apps)$/', Request::path()) !== false && static::isAllowedIP());
    }

    public function before() {
        $is_commandline = true; //(Request::header('User-Agent') == 'hook-cli');

        $key = AppContext::getKey();
        $allowed = $is_commandline && ($key && $key->isCommandline());

        if (!static::isRootOperation() && !$allowed) {
            throw new UnauthorizedException("Your IP Address is not allowed to perform this operation.");
        }
    }

    public function index() {
        AppContext::setTablePrefix('');
        return $this->json(Model\App::all());
    }

    public function create() {
        // Reset table prefix
        AppContext::setTablePrefix('');

        $data = Model\App::create(Input::get('app'));
        $response = $data->toArray();

        // Set application prefix for migration
        AppContext::setKey($data->keys[0]);
        AppContext::migrate();

        return $this->json($response);
    }

    public function delete_cache() {
        Cache\Cache::flush();
        return $this->json(array('success' => true));
    }

    public function logs() {
        $file_path = storage_dir() . '/logs.txt';
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
        return $this->json(array('text' => $content));
    }

    public function tasks() {
        return $this->json(Model\ScheduledTask::all()->toArray());
    }

    public function recreate_tasks() {
        // Remove all scheduled tasks for this app
        return $this->json(array(
            'success' => Model\ScheduledTask::deploy(Input::get('schedule', array()))
        ));
    }

    public function dump_deploy() {
        return $this->json(array(
            'modules' => Model\Module::dump(),
            'packages' => Package\Manager::dump()
        ));
    }

    public function deploy() {
        set_time_limit(0);

        // application configs
        Model\AppConfig::deploy(Input::get('config', array()));

        // application secrets
        Model\AppConfig::deploy(Input::get('security', array()), array('security'));

        // invalidate previous configurations
        Model\AppConfig::where('updated_at', '<', Carbon::now())->delete();

        $collections_migrated = 0;

        // Flush cache on deployment
        Cache\Cache::flush();

        // Migrate and keep schema cache
        foreach(Input::get('schema', array()) as $collection => $config) {
            if (Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config)) {
                $collections_migrated += 1;
            }
        }

        return $this->json(array(
            // schema
            'schema' => $collections_migrated,

            // scheduled tasks
            'schedule' => Model\ScheduledTask::deploy(Input::get('schedule', array())),

            // modules
            'modules' => Model\Module::deploy(Input::get('modules', array())),

            // install composer packages
            'packages' => Package\Manager::install(Input::get('packages', array()))
        ));

    }

    public function configs() {
        return $this->json(Model\AppConfig::all());
    }

    public function modules() {
        return $this->json(Model\Module::all());
    }

    public function schema() {
        return $this->json(Schema\Builder::dump());
    }

    public function upload_schema() {
        $schema = Input::get();

        foreach($schema as $collection => $config) {
            Schema\Builder::migrate(Model\App::collection($collection)->getModel(), $config);
        }

        return $this->json(array('success' => true));
    }

    public function delete() {
        return $this->json(array('success' => false));
    }


    public static function isAllowedIP() {
        $allowed = false;
        $allowed_ip_addresses = AppContext::config('allowed_ip_addresses');

        if ($allowed_ip_addresses && !empty($allowed_ip_addresses)) {
            $allowed = in_array("*", $allowed_ip_addresses) ||
                in_array(Request::ip(), $allowed_ip_addresses);
        }

        return $allowed;
    }

}
