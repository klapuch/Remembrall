<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Page.php';

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Routing;
use Klapuch\Uri;

const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	ROUTES = __DIR__ . '/../App/Configuration/routes.ini',
	LOGS = __DIR__ . '/../log';
echo (new Remembrall\Page(
	new Ini\Valid(CONFIGURATION, new Ini\Typed(CONFIGURATION)),
	new Log\FilesystemLogs(new Log\DynamicLocation(new Log\DirectoryLocation(LOGS))),
	new Routing\HttpRoutes(new Ini\Valid(ROUTES, new Ini\Typed(ROUTES))),
	new Uri\BaseUrl($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']),
	$_SERVER['REQUEST_METHOD'],
	in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)
		? ${'_' . $_SERVER['REQUEST_METHOD']}
		: []
))->load();