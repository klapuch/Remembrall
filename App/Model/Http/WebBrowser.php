<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Dibi;
use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\{
	Storage, Subscribing
};

/**
 * Browser sending a http request and receiving http response
 */
final class WebBrowser implements Browser {
	private $http;
	private $database;

	public function __construct(
		GuzzleHttp\ClientInterface $http,
		Dibi\Connection $database
	) {
		$this->http = $http;
		$this->database = $database;
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
			(new Storage\Transaction($this->database))->start(
				function() use ($headers, $response) {
					$this->database->query(
						'INSERT INTO pages (url, content) VALUES
						(?, ?) ON DUPLICATE KEY UPDATE
						content = VALUES(content)',
						$headers['host'],
						$response->content()
					);
					$this->database->query(
						'INSERT INTO page_visits (page_url, visited_at) VALUES
						(?, ?)',
						$headers['host'],
						new \DateTimeImmutable()
					);
				}
			);
			return new Subscribing\AvailableWebPage(
				new Subscribing\HtmlWebPage($request, $response),
				$response
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