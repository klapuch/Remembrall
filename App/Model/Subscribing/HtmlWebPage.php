<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use GuzzleHttp;
use Psr\Http\Message;
use Remembrall\Exception;

/**
 * Fresh html page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $http;

	public function __construct(GuzzleHttp\ClientInterface $http) {
		$this->http = $http;
	}

	public function content(): \DOMDocument {
		$response = $this->http->request('GET');
		if(!$this->isHTML($response))
			throw new Exception\ExistenceException('Web page must be HTML');
		$dom = new DOM();
		$dom->loadHTML((string)$response->getBody());
		return $dom;
	}

	public function url(): string {
		return $this->http->getConfig('base_uri');
	}

	/**
	 * Checks whether the page is HTML
	 * @param Message\ResponseInterface $response
	 * @return bool
	 */
	private function isHTML(Message\ResponseInterface $response): bool {
		return strcasecmp(
			$response->getHeader('Content-Type'),
			'text/html'
		) === 0;
	}
}