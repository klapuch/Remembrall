<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
use Klapuch\{
	Ini, Storage, Uri
};
const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini';
try {
	Tracy\Debugger::enable();
	mb_internal_encoding('UTF-8');
	$configuration = (new Ini\Valid(
		CONFIGURATION,
		new Ini\Typed(CONFIGURATION)
	))->read();
	foreach($configuration['INI'] as $name => $value)
		ini_set($name, (string)$value);
	date_default_timezone_set('Europe/Prague');
	session_start($configuration['SESSIONS']);
	session_regenerate_id(true);
	foreach($configuration['HEADERS'] as $field => $value)
		header(sprintf('%s:%s', $field, $value));
	$logger = new Tracy\Logger(__DIR__ . '/../Log');
	$url = new Uri\BaseUrl($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']);
	$path = explode('/', $url->path());
	$page = isset($path[0]) && $path[0] ? ucfirst($path[0]) : 'Default';
	$action = isset($path[1]) && $path[1] ? ucfirst($path[1]) : 'Default';
	$class = 'Remembrall\\Page\\' . $page . 'Page';
	$method = $_SERVER['REQUEST_METHOD'] === 'POST'
		? 'action' . $action
		: 'render' . $action;
	(new $class(
		$url,
		new Storage\MonitoredDatabase(
			new Storage\PDODatabase(
				$configuration['DATABASE']['dsn'],
				$configuration['DATABASE']['username'],
				$configuration['DATABASE']['password']
			)
		),
		$logger
	))->$method(
		$_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST
	);
} catch(Throwable $ex) {
	$logger->log($ex, Tracy\Logger::WARNING);
	echo 'Error';
}
