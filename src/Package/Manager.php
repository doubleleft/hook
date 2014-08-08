<?php namespace Hook\Package;

use Hook\Database\AppContext;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class Manager {
    const VENDOR_DIR = 'vendor';
    const VENDOR_TMP_DIR = 'vendor-tmp';

    public static function install($packages) {
        putenv('COMPOSER_HOME=' . __DIR__ . '/../../vendor/bin/composer');
        self::createComposerJson($packages);
        chdir(storage_dir());

        //
        // Programmatically run `composer install`
        //
        $input = new ArrayInput(array('command' => 'install'));
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run($input);

        // remove previously installed packages
        if (file_exists(storage_dir() . '/' . self::VENDOR_DIR)) {
            rmdir_r(storage_dir() . '/' . self::VENDOR_DIR);
        }

        // move newsly installed packages to `production`
        rename(
            storage_dir() . '/' . self::VENDOR_TMP_DIR,
            storage_dir() . '/' . self::VENDOR_DIR
        );

        return $code;
    }

    protected static function createComposerJson($packages) {
        $composer_json = str_replace("\/", '/', json_encode(array(
            'config' => array('vendor-dir' => self::VENDOR_TMP_DIR),
            'require' => $packages
        )));
        return file_put_contents(storage_dir() . '/composer.json', $composer_json);
    }

}
