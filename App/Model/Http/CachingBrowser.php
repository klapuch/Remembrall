<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Dibi;
use Remembrall\Model\{
	Storage, Subscribing
};

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

	public function send(Request $request): Subscribing\Page {
		$url = $request->headers()->header('host')->value();
		if(!$this->cached($url))
			return $this->origin->send($request);
		return new Subscribing\ConstantPage(
			$url,
			$this->database->fetchSingle(
				'SELECT content FROM pages WHERE url = ?',
				$url
			)
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
}