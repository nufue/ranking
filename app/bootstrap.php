<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['89.190.44.216', '85.132.140.57', '127.0.0.1', '10.10.1.90', '10.10.1.100']);
$configurator->enableTracy(__DIR__ . '/../log', 'jiri+plavana@hrazdil.info');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');
$configurator->addConfig(__DIR__ . '/config/authenticator.neon');

$container = $configurator->createContainer();

return $container;