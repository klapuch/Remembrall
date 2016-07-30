<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Tracy;
use Dibi;
use GuzzleHttp;
use Nette\Caching\Storages;
use Remembrall\Model\Http;

/**
 * Online parts on the internet, it means, they are freshly downloaded
 */
final class OnlineParts implements Parts {
	private $origin;
	private $logger;
	private $database;
	private $http;

	public function __construct(
		Parts $origin,
		Tracy\ILogger $logger,
		Dibi\Connection $database,
		GuzzleHttp\ClientInterface $http
	) {
		$this->origin = $origin;
		$this->logger = $logger;
		$this->database = $database;
		$this->http = $http;
	}

	public function add(Part $part, string $url, string $expression): Part {
		$this->origin->add($part, $url, $expression);
	}

	public function iterate(): array {
		return array_reduce(
			$this->origin->iterate(),
			function($previous, Part $part) {
				$visualPart = $part->print();
				/**
				 * @var string $url
				 * @var string $expression
				 * @var Page $oldPage
				 */
				list($url, $expression, $oldPage) = [
					$visualPart['url'],
					(string)$visualPart['expression'],
					$visualPart['page'],
				];
				$onlinePage = (new Http\LoggedRequest(
					new Http\CachedRequest(
						new Http\FrugalRequest(
							new Http\DefaultRequest(
								$this->http,
								new GuzzleHttp\Psr7\Request('GET', $url)
							),
							$url,
							new WebPages($this->database),
							$this->database
						),
						new Storages\MemoryStorage()
					),
					$this->logger
				))->send();
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression($onlinePage, $expression),
						new ConstantPage(
							$onlinePage,
							$oldPage->content()->saveHTML()
						)
					),
					$part->content(),
					$url
				);
				return $previous;
			}
		);
	}
}
