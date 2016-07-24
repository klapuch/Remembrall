<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;

/**
 * Default http request
 */
final class DefaultRequest implements Request {
	private $http;
	private $request;

	public function __construct(
		GuzzleHttp\ClientInterface $http,
		Message\RequestInterface $request
	) {
		$this->http = $http;
		$this->request = $request;
	}

	public function send(): Subscribing\Page {
		try {
			$response = $this->http->send($this->request);
			return new Subscribing\HtmlWebPage(
				new HtmlResponse(
					new AvailableResponse($response),
					$response
				),
				$this
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