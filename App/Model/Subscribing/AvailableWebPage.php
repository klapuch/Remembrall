<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use GuzzleHttp;
use Psr\Http\Message;
use Remembrall\Exception;

/**
 * Page can not send codes which temporary block access to it
 */
final class AvailableWebPage implements Page {
	private $origin;
	private $http;

	public function __construct(
		Page $origin,
		GuzzleHttp\ClientInterface $http
	) {
		$this->origin = $origin;
		$this->http = $http;
	}

	public function content(): \DOMDocument {
		$response = $this->http->request('GET');
		if($this->available($response))
			return $this->origin->content();
		throw new Exception\ExistenceException(
			sprintf(
				'Web page "%s" can not be loaded because of %d - %s',
				$this->url(),
				$response->getStatusCode(),
				$response->getReasonPhrase()
			)
		);
	}

	public function url(): string {
		return $this->origin->url();
	}

	/**
	 * Checks whether the page is normally accessible
	 * It means status code under 400
	 * @param Message\ResponseInterface $response
	 * @return bool
	 */
	private function available(Message\ResponseInterface $response): bool {
		return $response->getStatusCode() < 400;
	}
}