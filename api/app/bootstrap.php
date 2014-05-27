<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'log.enabled' => true
));

// database
require __DIR__ . '/bootstrap/connection.php';

// core
require __DIR__ . '/core/functions.php';

//
// WARNING: MonkeyPatch on ApnsPHP autoloader
//
// Need to update this when the pull request is accepted:
// https://github.com/duccio/ApnsPHP/pull/15
//
spl_autoload_unregister('ApnsPHP_Autoload');
function ApnsPHP_Autoload_Patched($sClassName) {
	if (empty($sClassName)) { throw new Exception('Class name is empty'); }

	$sPath = dirname(dirname(__FILE__)) . '/vendor/duccio/apns-php';
	if (empty($sPath)) { throw new Exception('Current path is empty'); }

	$sFile = sprintf('%s%s%s.php', $sPath, DIRECTORY_SEPARATOR, str_replace('_', DIRECTORY_SEPARATOR, $sClassName));
	if (is_file($sFile) && is_readable($sFile)) {
		require_once $sFile;
	}
}
spl_autoload_register('ApnsPHP_Autoload_Patched');

return $app;
