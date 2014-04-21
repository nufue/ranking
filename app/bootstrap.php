<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->setDebugMode(array('212.4.129.229', '62.168.39.34'));
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('<rok [0-9]{4}>/zavodnik/<id>', 'Zavodnik:default');
$container->router[] = new Route('<rok [0-9]{4}>/zavody/add', 'Zavody:add');
$container->router[] = new Route('<rok [0-9]{4}>/zavody/<id>', 'Zavody:detail');
$container->router[] = new Route('<rok [0-9]{4}>/<typ u23|u18|u14|zeny|u10>[/<show>]', array('presenter' => 'Homepage', 'action' => 'default'));
$container->router[] = new Route('<rok [0-9]{4}>/soupisky/<liga>', array('presenter' => 'Soupisky', 'action' => 'detail'));
$container->router[] = new Route('<rok [0-9]{4}>/<presenter>/<action>[/<id>]', 'Homepage:default');
$container->router[] = new Route('<rok [0-9]{4}>/excel-export', 'Homepage:excelExport');


$container->router[] = new Route('zavodnik/<id>', 'Zavodnik:default');
$container->router[] = new Route('zavody/add', 'Zavody:add');
$container->router[] = new Route('zavody/<id>', 'Zavody:detail');

$container->router[] = new Route('<typ u23|u18|u14|zeny|u10>[/<show>]', array('presenter' => 'Homepage', 'action' => 'default'));
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
$container->application->run();
