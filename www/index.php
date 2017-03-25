<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';
use Klapuch\{
	Ini, Storage, Uri, Log, Encryption, Output, Routing, Application
};
const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	ROUTES = __DIR__ . '/../App/Configuration/routes.ini';
const TEMPLATES = __DIR__ . '/../App/Page/templates';
try {
	$logs = new Log\FilesystemLogs(
		new Log\DynamicLocation(
			new Log\DirectoryLocation(__DIR__ . '/../log')
		)
	);
	$configuration = (new Ini\Valid(
		CONFIGURATION,
		new Ini\Typed(CONFIGURATION)
	))->read();
	(new Application\CombinedExtension(
		new Application\InternationalExtension('Europe/Prague'),
		new Application\IniSetExtension($configuration['INI']),
		new Application\SessionExtension($configuration['SESSIONS']),
		new Application\HeaderExtension($configuration['HEADERS'])
	))->improve();
	$url = new Uri\BaseUrl($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']);
	$route = (new Routing\HttpRoutes(
		new Ini\Valid(
			ROUTES,
			new Ini\Typed(ROUTES)
		)
	))->match($url);
	[$resource, $action, $parameters] = [$route->resource(), $route->action(), $route->parameters()];
	$class = (new Routing\MappedRoute($route, 'Remembrall\Page', 'Page'))->resource();
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
	$submit = 'submit' . $action;
	$target->startup();
	if($_SERVER['REQUEST_METHOD'] === 'POST' && method_exists($target, $submit))
		$target->$submit($_POST, $parameters);
	$xml = new \DOMDocument();
	$xml->load(TEMPLATES . sprintf('/../%s/templates/%s.xml', $resource, $action));
	echo (new Output\XsltTemplate(
		TEMPLATES . sprintf('/../%s/templates/%s.xsl', $resource, $action),
		new Output\MergedXml($xml, ...$target->template($parameters))
	))->render(['base_url' => $url->reference()]);
} catch(Throwable $ex) {
	throw $ex;
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