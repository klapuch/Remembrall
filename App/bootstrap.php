<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
$configuration = new Nette\Configurator();
$configuration->setDebugMode(true);
$configuration->enableDebugger(__DIR__ . '/../Log');
return $configuration->setTempDirectory(__DIR__ . '/../Temporary')
	->setTimeZone('Europe/Prague')
	->addConfig(__DIR__ . '/Configuration/config.neon')
	->createContainer();