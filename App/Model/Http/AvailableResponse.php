<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Psr\Http\Message;
use Remembrall\Exception;

/**
 * Response with available content
 */
final class AvailableResponse implements Response {
	private $response;

	public function __construct(Message\ResponseInterface $response) {
		$this->response = $response;
	}

	public function content(): string {
		if($this->response->getStatusCode() < 400)
			return (string)$this->response->getBody();
		throw new Exception\NotFoundException(
			sprintf(
				'Content could not be retrieved because of "%s"',
				sprintf(
					'%d %s',
					$this->response->getStatusCode(),
					$this->response->getReasonPhrase()
				)
			)
		);
	}
}