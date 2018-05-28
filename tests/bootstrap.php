<?php

require __DIR__ . '/../vendor/autoload.php';

if (getenv(\Tester\Environment::RUNNER)) {
	Tester\Environment::setup();
}

$configurator = new Nette\Configurator;
$configurator->setDebugMode(true);
$configurator->setTempDirectory(__DIR__ . '/../temp_test');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../app')
	->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

return $configurator->createContainer();
