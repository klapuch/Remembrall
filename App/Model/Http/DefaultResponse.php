<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Psr\Http\Message;

final class DefaultResponse implements Response {
	private $response;

	public function __construct(Message\ResponseInterface $response) {
		$this->response = $response;
	}

	public function headers(): Headers {
		$headers = $this->response->getHeaders();
		return new UniqueHeaders(
			array_reduce(
				array_keys($headers),
				function($previous, string $field) use ($headers) {
					$previous[$field] = current($headers[$field]);
					return $previous;
				}
			) + $this->additionalHeaders()
		);
	}

	public function content(): string {
		return (string)$this->response->getBody();
	}

	/**
	 * Additional headers for the current response
	 * @return array
	 */
	private function additionalHeaders(): array {
		return [
			'Status' => sprintf(
				'%d %s',
				$this->response->getStatusCode(),
				$this->response->getReasonPhrase()
			),
			'Protocol' => $this->response->getProtocolVersion(),
		];
	}
}