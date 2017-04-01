<?php
declare(strict_types = 1);
namespace Remembrall;

use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;
use Remembrall\Page\BasePage;

final class Page {
	private $configuration;
	private $logs;
	private $routes;
	private $uri;
	private $method;
	private $request;

	public function __construct(
		Ini\Ini $configuration,
		Log\Logs $logs,
		Routing\Routes $routes,
		Uri\Uri $uri,
		string $method,
		array $request
	) {
		$this->configuration = $configuration;
		$this->logs = $logs;
		$this->routes = $routes;
		$this->uri = $uri;
		$this->method = $method;
		$this->request = $request;
	}

	private function target(Routing\Route $route, array $configuration): BasePage {
		$class = (new Routing\MappedRoute(
			$route,
			$configuration['MAPPING']['namespace'],
			$configuration['MAPPING']['resolution']
		))->resource();
		return new $class(
			$this->uri,
			new Storage\SafePDO(
				$configuration['DATABASE']['dsn'],
				$configuration['DATABASE']['username'],
				$configuration['DATABASE']['password']
			),
			$this->logs,
			new Encryption\AES256CBC($configuration['KEYS']['password'])
		);
	}

	private function content(Routing\Route $route, BasePage $target, array $configuration): string {
		$template = sprintf(
			'%s/../%s/templates/%s',
			$configuration['PATHS']['templates'],
			$route->resource(),
			$route->action()
		);
		$xml = new \DOMDocument();
		$xml->load(sprintf('%s.xml', $template));
		return (new Output\XsltTemplate(
			sprintf('%s.xsl', $template),
			new Output\MergedXml($xml, ...$target->template($route->parameters()))
		))->render(['base_url' => $this->uri->reference()]);
	}

	private function interact(Routing\Route $route, BasePage $target): void {
		$submit = 'submit' . $route->action();
		if ($this->method === 'POST' && method_exists($target, $submit))
			$target->$submit($this->request, $route->parameters());
	}

	public function load(): string {
		try {
			$configuration = $this->configuration->read();
			(new Application\CombinedExtension(
				new Application\InternationalExtension('Europe/Prague'),
				new Application\IniSetExtension($configuration['INI']),
				new Application\SessionExtension($configuration['SESSIONS']),
				new Application\HeaderExtension($configuration['HEADERS'])
			))->improve();
			$route = $this->routes->match($this->uri);
			$target = $this->target($route, $configuration);
			$target->startup();
			$this->interact($route, $target);
			return $this->content($route, $target, $configuration);
		} catch (\Throwable $ex) {
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::ERROR)
					)
				)
			);
			http_response_code(500);
			header('Location: error');
			exit;
		}
	}
}