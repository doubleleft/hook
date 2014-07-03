<?php
namespace API\Model;

/**
 * Task scheduled to run on target time
 */
class ScheduledTask extends Model
{

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

        // TODO: redirect output to application log file.
        // https://github.com/doubleleft/dl-api/issues/37

        $app_id = App::currentId();

        $curl_headers = "-H 'X-App-Id: {$app_id}' ";
        $curl_headers .= "-H 'X-App-Key: {$this->app->keys[0]->key}' ";
        $curl_headers .= "-H 'X-Scheduled-Task: yes' ";

        return $schedule . ' ' . "curl -XGET {$curl_headers} '{$public_url}' 2>&1 /dev/null";
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['command'] = $this->getCommand();

        return $arr;
    }

    public static function install()
    {
        exec('cat ' . __DIR__ . '/../storage/crontabs/*.cron | crontab', $output, $return_code);

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
