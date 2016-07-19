<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;

/**
 * Default http request
 */
final class DefaultRequest implements Request {
	private $http;
	private $headers;

	public function __construct(
		GuzzleHttp\ClientInterface $http,
		Headers $headers
	) {
		$this->http = $http;
		$this->headers = $headers;
	}

	public function send(): Subscribing\Page {
		try {
			$response = new DefaultResponse(
				$this->http->request(
					$this->headers->header('method')->value(),
					$this->headers->header('host')->value(),
					array_reduce(
						$this->headers->iterate(),
						function($previous, Header $header) {
							$previous[$header->field()] = $header->value();
							return $previous;
						}
					)
				)
			);
			return new Subscribing\AvailableWebPage(
				new Subscribing\HtmlWebPage($response, $this),
				$response
			);
		} catch(RequestException $ex) {
			throw new NotFoundException(
				'Page could not be retrieved. Does the URL really exist?',
				$ex->getCode(),
				$ex
			);
		}
	}
}