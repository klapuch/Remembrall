<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Dibi;
use Remembrall\Model\Subscribing;

/**
 * Frugal request firstly check database with a content
 * If the content is outdated, then real request is sent
 */
final class FrugalRequest implements Request {
	private $origin;
	private $headers;
	private $pages;
	private $database;
	const EXPIRATION = 'PT10M';

	public function __construct(
		Request $origin,
		Headers $headers,
		Subscribing\Pages $pages,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->headers = $headers;
		$this->pages = $pages;
		$this->database = $database;
	}

	public function send(): Subscribing\Page {
		$url = $this->headers->header('host')->value();
		if($this->outdated($url))
			return $this->pages->add($url, $this->origin->send());
		return new Subscribing\ConstantPage(
			$this->database->fetchSingle(
				'SELECT content FROM pages WHERE url = ?',
				$url
			)
		);
	}

	/**
	 * Is the response still available (valid) in the database?
	 * @param string $url
	 * @return bool
	 */
	private function outdated(string $url): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM page_visits
			RIGHT JOIN pages ON pages.url = page_visits.page_url
			WHERE page_url IS NULL OR page_url = ?
			AND (
				SELECT visited_at
				FROM page_visits
				WHERE page_url = ?
				ORDER BY visited_at DESC
				LIMIT 1
		  	) + INTERVAL ? MINUTE < NOW()
			LIMIT 1',
			$url,
			$url,
			(new \DateInterval(self::EXPIRATION))->i
		);
	}
}