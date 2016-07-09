<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Dibi;
use Remembrall\Model\Storage;

/**
 * Caching browser
 * Does not send a request in case the database already owns valid one
 */
final class CachingBrowser implements Browser {
	private $origin;
	private $database;
	const EXPIRATION = 'PT10M';

	public function __construct(Browser $origin, Dibi\Connection $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function send(Request $request): Response {
		$url = $request->headers()->header('host')->value();
		if(!$this->cached($url)) {
			$response = $this->origin->send($request);
			$this->cache($url, $response);
			return $response;
		}
		$response = $this->database->fetch(
			'SELECT content, headers FROM pages WHERE url = ?',
			$url
		);
		return new ConstantResponse(
			new UniqueHeaders(unserialize($response['headers'])),
			$response['content']
		);
	}

	/**
	 * Is response by the url still cached?
	 * @param string $url
	 * @return bool
	 */
	private function cached(string $url): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM page_visits
			INNER JOIN pages ON pages.ID = page_visits.page_id
			WHERE page_id = (SELECT ID FROM pages WHERE url = ?)
			AND headers != ""
			AND visited_at + INTERVAL ? MINUTE >= NOW()',
			$url,
			(new \DateInterval(self::EXPIRATION))->i
		);
	}

	/**
	 * Cache the given response
	 * @param string $url
	 * @param Response $response
	 * @throws \Throwable
	 * @return void
	 */
	private function cache(string $url, Response $response) {
		(new Storage\Transaction($this->database))->start(
			function() use ($url, $response) {
				$this->database->query(
					'INSERT INTO pages (url, content, headers) VALUES (?, ?, ?)
					ON DUPLICATE KEY UPDATE
					content = VALUES(content), headers = VALUES(headers)',
					$url,
					$response->content(),
					serialize(
						array_reduce(
							$response->headers()->iterate(),
							function($previous, Header $header) {
								$previous[$header->field()] = $header->value();
								return $previous;
							}
						)
					)
				);
				$this->database->query(
					'INSERT INTO page_visits (page_id, visited_at) VALUES
					((SELECT ID FROM pages WHERE url = ?), ?)',
					$url,
					new \DateTimeImmutable()
				);
			}
		);
	}
}