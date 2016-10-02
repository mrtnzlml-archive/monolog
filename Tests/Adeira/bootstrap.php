<?php

require __DIR__ . '/../../Vendor/autoload.php';

// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));
Tester\Helpers::purge(TEMP_DIR);
Tracy\Debugger::$logDirectory = TEMP_DIR;

Testbench\Bootstrap::setup(TEMP_DIR, function(\Nette\Configurator $configurator) {
	$configurator->addConfig(__DIR__ . '/tests.neon');
});
