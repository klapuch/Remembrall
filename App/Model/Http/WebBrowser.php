<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message;
use Remembrall\Exception\ExistenceException;

/**
 * Browser sending a http request and receiving http response
 */
final class WebBrowser implements Browser {
	private $http;

	public function __construct(GuzzleHttp\ClientInterface $http) {
		$this->http = $http;
	}

	public function send(Request $request): Response {
		try {
			$headers = $request->headers();
			return new DefaultResponse(
				$this->http->request(
					$headers->header('method')->value(),
					$headers->header('host')->value(),
					array_reduce(
						$headers->iterate(),
						function($previous, Header $header) {
							$previous[$header->field()] = $header->value();
							return $previous;
						}
					)
				)
			);
		} catch(RequestException $ex) {
			throw new ExistenceException(
				'Connection could not be established. Does the URL really exist?',
				$ex->getCode(),
				$ex
			);
		}
	}
}