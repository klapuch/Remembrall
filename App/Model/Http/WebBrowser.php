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
	}
}