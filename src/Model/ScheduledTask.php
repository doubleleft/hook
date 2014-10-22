<?php
namespace Hook\Model;

use Hook\Application\Context as Context;
use Hook\Model\AppKey as AppKey;

/**
 * Task scheduled to run on target time
 */
class ScheduledTask extends Model
{

    public static function deploy($schedule)
    {
        // Don't proceed if there's nothing to schedule
        if (static::count() == 0 && count($schedule) == 0) {
            return false;
        }

        $cronfile = shared_storage_dir() . '/' . Context::getAppId(). '.cron';
        $previous_tasks = (file_exists($cronfile)) ? file_get_contents($cronfile) : "";
        $new_tasks = '';

        // Remove all scheduled tasks for this app
        static::truncate();
        foreach ($schedule as $task) {
            $task = ScheduledTask::create($task);
            $new_tasks .= $task->getCommand() . "\n";
        }
        file_put_contents($cronfile, $new_tasks);
        static::install();

        return $previous_tasks != $new_tasks;
    }

    public function getCommand()
    {
        $shortcuts = array(
            'hourly'  => '0 * * * *',
            'daily'   => '0 0 * * *',
            'monthly' => '0 0 1 * *',
            'weekly'  => '0 0 * * 0'
        );
        $schedule = preg_match('/[a-z]/', $this->schedule) ? $shortcuts[$this->schedule] : $this->schedule;

        $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http');
        // $public_url = $protocol . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '/' . $this->task;
        $public_url = $protocol . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '/' . $this->task;

        // retrieve server key to allow calls from crontab.
        $server_key = Context::getAppKeys(AppKey::TYPE_SERVER)->first();

        $curl_headers = "-H 'X-App-Id: {$server_key->app_id}' ";
        $curl_headers .= "-H 'X-App-Key: {$server_key->key}' ";
        $curl_headers .= "-H 'X-Scheduled-Task: yes' ";

        // Output the response to application log file
        $app = \Slim\Slim::getInstance();
        $output_file = $app->log->getWriter()->getFilePath();

        // Redirect stderr and stdout to file
        return $schedule . ' ' . "curl -XGET {$curl_headers} '{$public_url}' &> " . $output_file;
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['command'] = $this->getCommand();

        return $arr;
    }

    public static function install()
    {
        exec('cat ' . shared_storage_dir() . '/*.cron | crontab', $output, $return_code);

        if (!empty($output)) {
            throw new Exception(json_encode($output));
        }

        return $return_code === 0;

        // $tasks = array();
        // static::all()->each(function ($task) use (&$tasks) {
        // 	array_push($tasks, $task->toString());
        // });
        // file_put_contents(__DIR__ . '/../storage/crontab', join("\n", $tasks));
    }

}
