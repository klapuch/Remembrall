<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use GuzzleHttp;
use Remembrall\Exception;

final class ExistingWebPage implements Page {
	private $origin;
	private $http;
	const NOT_FOUND = 404;

	public function __construct(
		Page $origin,
		GuzzleHttp\ClientInterface $http
	) {
		$this->origin = $origin;
		$this->http = $http;
	}

	public function content(): \DOMDocument {
		if($this->exists())
			return $this->origin->content();
		throw new Exception\ExistenceException(
			sprintf(
				'Web page with the "%s" address does not exist',
				$this->url()
			)
		);
	}

	public function url(): string {
		return $this->origin->url();
	}

	/**
	 * Checks whether the page exists
	 * @return bool
	 */
	private function exists(): bool {
		$response = $this->http->request('GET');
		return $response->getStatusCode() !== self::NOT_FOUND;
	}
}