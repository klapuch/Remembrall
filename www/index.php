<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
use Klapuch\{
	Ini, Storage
};
const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini';
$configuration = (new Ini\Valid(
	CONFIGURATION,
	new Ini\Typed(CONFIGURATION)
))->read();
$pathname = explode('/', $_SERVER['REQUEST_URI'], 5);
$page = isset($pathname[3]) && $pathname[3]
	? trim(ucfirst(strtolower($pathname[3])), '/')
	: 'Default';
$action = isset($pathname[4]) && $pathname[4]
	? trim(ucfirst(strtolower($pathname[4])), '/')
	: 'Default';
$class = 'Remembrall\\Page\\' . $page . 'Page';
$method = $_SERVER['REQUEST_METHOD'] === 'POST'
	? 'action' . $action
	: 'render' . $action;
try {
	(new $class(
		new Storage\PDODatabase(
			$configuration['DATABASE']['dsn'],
			$configuration['DATABASE']['username'],
			$configuration['DATABASE']['password']
		),
		new Tracy\Logger(__DIR__ . '/../Log')
	))->$method();
} catch(Throwable $ex) {
	echo $ex->getMessage();
}
