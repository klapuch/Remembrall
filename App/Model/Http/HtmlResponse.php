<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Psr\Http\Message;
use Remembrall\Exception;

/**
 * Response in HTML format
 */
final class HtmlResponse implements Response {
	private $response;
	private $origin;
	const ALLOWED_CONTENT_TYPE = 'text/html';

	public function __construct(
		Response $origin,
		Message\ResponseInterface $response
	) {
		$this->origin = $origin;
		$this->response = $response;
	}

	public function content(): string {
		if($this->isHtml())
			return $this->origin->content();
		throw new Exception\NotFoundException('Response must be in HTML');
	}

	/**
	 * Is the response in html format?
	 * @return bool
	 */
	private function isHtml(): bool {
		$contentType = current($this->response->getHeader('Content-Type'));
		if(!empty($contentType)) {
			if($contentType !== self::ALLOWED_CONTENT_TYPE) {
				foreach(array_map('trim', explode(';', $contentType)) as $value)
					if(strcasecmp($value, self::ALLOWED_CONTENT_TYPE) === 0)
						return true;
				return false;
			}
			return true;
		}
		return false;
	}
}