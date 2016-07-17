<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;

/**
 * Browser sending a http request and receiving http response
 */
final class WebBrowser implements Browser {
	private $http;
	private $pages;

	public function __construct(
		GuzzleHttp\ClientInterface $http,
		Subscribing\Pages $pages
	) {
		$this->http = $http;
		$this->pages = $pages;
	}

	public function send(Request $request): Subscribing\Page {
		try {
			$headers = array_reduce(
				$request->headers()->iterate(),
				function($previous, Header $header) {
					$previous[$header->field()] = $header->value();
					return $previous;
				}
			);
			$response = new DefaultResponse(
				$this->http->request(
					$headers['method'],
					$headers['host'],
					$headers
				)
			);
			return $this->pages->add(
				new Subscribing\AvailableWebPage(
					new Subscribing\HtmlWebPage($request, $response),
					$response
				)
			);
		} catch(RequestException $ex) {
			throw new NotFoundException(
				'Connection could not be established. Does the URL really exist?',
				$ex->getCode(),
				$ex
			);
		}
	}
}