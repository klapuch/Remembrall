<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use GuzzleHttp;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Remembrall\Exception\NotFoundException;

/**
 * Fresh html page downloaded from the internet
 */
final class HtmlWebPage implements Page {
	private $url;
	private $http;
	const ALLOWED_CONTENT_TYPE = 'text/html';

	public function __construct(string $url, GuzzleHttp\ClientInterface $http) {
		$this->url = $url;
		$this->http = $http;
	}

	public function content(): \DOMDocument {
		try {
			$response = $this->http->send(new Request('GET', $this->url));
			if(!$this->isAvailable($response)) {
				throw new NotFoundException(
					sprintf(
						'Content could not be retrieved because of "%s"',
						sprintf(
							'%d %s',
							$response->getStatusCode(),
							$response->getReasonPhrase()
						)
					)
				);
			} elseif(!$this->isHtml($response)) {
				throw new NotFoundException(
					sprintf('Page "%s" is not in HTML format', $this->url)
				);
			}
			$dom = new DOM();
			$dom->loadHTML((string)$response->getBody());
			return $dom;
		} catch(RequestException $ex) {
			throw new NotFoundException(
				sprintf(
					'Page "%s" is unreachable. Does the URL exist?',
					$this->url
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function refresh(): Page {
		return new self($this->url, $this->http);
	}

	/**
	 * Is the response in html format?
	 * @param ResponseInterface $response
	 * @return bool
	 */
	private function isHtml(ResponseInterface $response): bool {
		$contentType = current($response->getHeader('Content-Type'));
		if(!empty($contentType)) {
			if($contentType !== self::ALLOWED_CONTENT_TYPE) {
				foreach(explode(';', $contentType) as $value)
					if(strcasecmp($value, self::ALLOWED_CONTENT_TYPE) === 0)
						return true;
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Is the page without error and therefore available?
	 * @param ResponseInterface $response
	 * @return bool
	 */
	private function isAvailable(ResponseInterface $response) {
		return $response->getStatusCode() < 400;
	}
}