<?php namespace Hook\Package;

use Hook\Database\AppContext;

use Composer\Factory;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Manager {
    const VENDOR_DIR = 'vendor';
    const VENDOR_TMP_DIR = 'vendor-tmp';

    public static function install($packages) {
        // Don't proceed if packages haven't changed.
        if ($packages == self::dump()) { return false; }

        putenv('COMPOSER_HOME=' . __DIR__ . '/../../vendor/bin/composer');
        self::createComposerJson($packages);
        chdir(storage_dir());

        // Setup composer output formatter
        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);

        //
        // Programmatically run `composer install`
        //
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(array('command' => 'install')), $output);

        // remove composer.json
        if (file_exists(storage_dir() . '/composer.lock')) {
            unlink(storage_dir() . '/composer.lock');
        }

        // rewind stream to read full contents
        rewind($stream);
        return stream_get_contents($stream);
    }

    public static function dump() {
        $composer_file = storage_dir() . '/composer.json';
        if (file_exists($composer_file)) {
            $composer_json = json_decode(file_get_contents($composer_file), true);
            return $composer_json['require'];
        } else {
            return array();
        }
    }

    public static function autoload() {
        $autoload_file = storage_dir() . '/vendor/autoload.php';
        if (file_exists($autoload_file)) {
            require $autoload_file;
        }
    }

    protected static function createComposerJson($packages) {
        $composer_json = str_replace("\/", '/', json_encode(array(
            'config' => array('vendor-dir' => self::VENDOR_DIR),
            'require' => $packages
        )));
        return file_put_contents(storage_dir() . '/composer.json', $composer_json);
    }

}
