<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
Tester\Environment::setup();
$_GET = []; // Because of UI tests
date_default_timezone_set('Europe/Prague');