<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;

final class WebBrowser implements Browser {
	private $http;

	public function __construct(GuzzleHttp\ClientInterface $http) {
		$this->http = $http;
	}

	public function send(Request $request): Response {
		$requestHeaders = $request->headers();
		$response = $this->http->request(
			$requestHeaders->header('method')->value(),
			$requestHeaders->header('host')->value(),
			array_reduce(
				$requestHeaders->iterate(),
				function($previous, Header $header) {
					$previous[$header->field()] = $header->value();
					return $previous;
				}
			)
		);
		$responseHeaders = $response->getHeaders();
		return new ConstantResponse(
			new UniqueHeaders(
				array_reduce(
					array_keys($responseHeaders),
					function($previous, string $field) use($responseHeaders) {
						$previous[$field] = current($responseHeaders[$field]);
						return $previous;
					}
				)
			),
			(string)$response->getBody()
		);
	}
}