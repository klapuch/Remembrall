<?php
declare(strict_types = 1);

$container = require __DIR__ . '/../App/bootstrap.php';
$container->getByType('Nette\Application\Application')->run();