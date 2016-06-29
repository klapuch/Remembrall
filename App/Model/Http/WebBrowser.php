<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\ConnectException;
use Remembrall\Exception\ExistenceException;
use Psr\Http\Message;

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
		} catch(ConnectException $ex) {
			throw new ExistenceException(
				'Given URL does not exist',
				$ex->getCode(),
				$ex
			);
		}
	}
}