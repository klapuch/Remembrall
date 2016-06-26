<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use Psr\Http\Message;

final class WebBrowser implements Browser {
	private $http;

	public function __construct(GuzzleHttp\ClientInterface $http) {
		$this->http = $http;
	}

	public function send(Request $request): Response {
		$headers = $request->headers();
		return $this->response(
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
	}

	/**
	 * Response given from querying host
	 * @param Message\ResponseInterface $response
	 * @return Response
	 */
	private function response(Message\ResponseInterface $response): Response {
		$headers = $response->getHeaders();
		$additionalHeaders = [
			'Status' => sprintf(
				'%d: %s',
				$response->getStatusCode(),
				$response->getReasonPhrase()
			),
		];
		return new ConstantResponse(
			new UniqueHeaders(
				array_reduce(
					array_keys($headers),
					function($previous, string $field) use ($headers) {
						$previous[$field] = current($headers[$field]);
						return $previous;
					}
				) + $additionalHeaders
			),
			(string)$response->getBody()
		);
	}
}