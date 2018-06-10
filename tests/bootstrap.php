<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator();

$configurator->setDebugMode(TRUE);
$configurator->enableTracy(__DIR__ . '/log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/temp');

$robotLoader = $configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../app')
	->addDirectory(__DIR__ . '/../tests');

$robotLoader->acceptFiles = '*.php, *.phpt';

$robotLoader->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');


$container = $configurator->createContainer();

return $container;