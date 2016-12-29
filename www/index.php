<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
use Klapuch\{
	Ini, Storage, Uri, Log, Encryption
};
const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini';
const TIMER = 'timer';
const ELAPSE = 20;
try {
	mb_internal_encoding('UTF-8');
	$logs = new Log\FilesystemLogs(
		new Log\DynamicLocation(
			new Log\DirectoryLocation(__DIR__ . '/../Log')
		)
	);
	$configuration = (new Ini\Valid(
		CONFIGURATION,
		new Ini\Typed(CONFIGURATION)
	))->read();
	foreach($configuration['INI'] as $name => $value)
		ini_set($name, (string)$value);
	date_default_timezone_set('Europe/Prague');
	session_start($configuration['SESSIONS']);
	if(isset($_SESSION[TIMER]) && (time() - $_SESSION[TIMER]) > ELAPSE) {
		$_SESSION[TIMER] = time();
		session_regenerate_id(true);
	} elseif(!isset($_SESSION[TIMER]))
		$_SESSION[TIMER] = time();
	foreach($configuration['HEADERS'] as $field => $value)
		header(sprintf('%s:%s', $field, $value));
	$url = new Uri\BaseUrl($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']);
	$path = explode('/', $url->path());
	$page = isset($path[0]) && $path[0] ? ucfirst($path[0]) : 'Default';
	$resource = isset($path[1]) && $path[1] ? ucfirst($path[1]) : 'Default';
	$class = 'Remembrall\\Page\\' . $page . 'Page';
	/** @var \Remembrall\Page\BasePage $target */
	$target = new $class(
		$url,
		new Storage\SafePDO(
			$configuration['DATABASE']['dsn'],
			$configuration['DATABASE']['username'],
			$configuration['DATABASE']['password']
		),
		$logs,
		new Encryption\AES256CBC($configuration['KEYS']['password'])
	);
	[$action, $render, $submit] = [
		'action' . $resource,
		'render' . $resource,
		'submit' . $resource,
	];
	$target->startup();
	if(method_exists($target, $action))
		$target->$action($_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST);
	if($_SERVER['REQUEST_METHOD'] === 'POST' && method_exists($target, $submit))
		$target->$submit($_POST);
	echo $target->render($page, lcfirst($resource), $_GET);
} catch(Throwable $ex) {
	$logs->put(
		new Log\PrettyLog(
			$ex,
			new Log\PrettySeverity(
				new Log\JustifiedSeverity(Log\Severity::ERROR)
			)
		)
	);
	echo 'Error has been logged';
}