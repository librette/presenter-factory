<?php
if (!$loader = include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}
$loader->addPsr4('LibretteTests\\Application\\PresenterFactory\\', __DIR__ . '/src/');

Tracy\Debugger::enable(Tracy\Debugger::DEVELOPMENT, __DIR__ . '/tmp/');
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
define('TEMP_DIR', __DIR__ . '/tmp/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));
Tester\Helpers::purge(TEMP_DIR);


$_SERVER = array_intersect_key($_SERVER, array_flip(['PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv']));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = $_GET = $_POST = [];

function run(Tester\TestCase $testCase)
{
	$testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}
