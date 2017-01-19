<?php

require __DIR__ . '/../../vendor/autoload.php';

// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/adeira_thread_' . getenv(Tester\Environment::THREAD));
Tester\Helpers::purge(TEMP_DIR);
Tracy\Debugger::$logDirectory = TEMP_DIR;

Testbench\Bootstrap::setup(TEMP_DIR, function(\Nette\Configurator $configurator) {
	$configurator->addConfig(__DIR__ . '/tests.neon');
});
