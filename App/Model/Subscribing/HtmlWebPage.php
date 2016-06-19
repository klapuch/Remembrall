<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use GuzzleHttp;
use Psr\Http\Message;
use Remembrall\Exception;

/**
 * Html page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $http;

	public function __construct(GuzzleHttp\ClientInterface $http) {
		$this->http = $http;
	}

	public function content(): \DOMDocument {
		$dom = new \DOMDocument();
		$response = $this->http->request('GET');
		if(!$this->isHTML($response))
			throw new Exception\ExistenceException('Web page must be HTML');
		@$dom->loadHTML((string)$response->getBody());
		return $dom;
	}

	public function url(): string {
		return $this->http->getConfig('base_uri');
	}

	/**
	 * Checks whether the page is HTML
	 * @return bool
	 */
	private function isHTML(Message\MessageInterface $response): bool {
		return strcasecmp(
			$response->getHeader('Content-Type'),
			'text/html'
		) === 0;
	}
}