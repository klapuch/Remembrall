<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
$configuration = new Nette\Configurator();
$configuration->enableDebugger(__DIR__ . '/../Log');
return $configuration->setDebugMode(true)
	->setTempDirectory(__DIR__ . '/../Temporary')
	->addConfig(__DIR__ . '/Configuration/config.neon')
	->addConfig(__DIR__ . '/Configuration/config.production.neon')
	->createContainer();