<?php namespace Hook\Package;

use Hook\Database\AppContext;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class Manager {
    const VENDOR_DIR = 'vendor';
    const VENDOR_TMP_DIR = 'vendor-tmp';

    public static function install($packages) {
        // if (empty($packages)) { return false; }

        putenv('COMPOSER_HOME=' . __DIR__ . '/../../vendor/bin/composer');
        self::createComposerJson($packages);
        chdir(storage_dir());

        //
        // Programmatically run `composer install`
        //
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(array('command' => 'install')));

        // remove previously installed packages
        if (file_exists(storage_dir() . '/' . self::VENDOR_DIR)) {
            rmdir_r(storage_dir() . '/' . self::VENDOR_DIR);
        }

        // move newsly installed packages to `production`
        if (file_exists(storage_dir() . '/' . self::VENDOR_TMP_DIR)) {
            rename(
                storage_dir() . '/' . self::VENDOR_TMP_DIR,
                storage_dir() . '/' . self::VENDOR_DIR
            );
        }

        // remove composer.json
        unlink(storage_dir() . '/composer.json');

        return $code == 0;
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
            'config' => array('vendor-dir' => self::VENDOR_TMP_DIR),
            'require' => $packages
        )));
        return file_put_contents(storage_dir() . '/composer.json', $composer_json);
    }

}
