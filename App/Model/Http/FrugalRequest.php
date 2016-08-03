<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Dibi;
use Remembrall\Model\Subscribing;

/**
 * Frugal request firstly check database with a content
 * If the content is outdated, then the real request is sent
 */
final class FrugalRequest implements Request {
	private $origin;
	private $url;
	private $pages;
	private $database;
	const EXPIRATION = 'PT10M';

	public function __construct(
		Request $origin,
		string $url,
		Subscribing\Pages $pages,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->url = $url;
		$this->pages = $pages;
		$this->database = $database;
	}

	public function send(): Subscribing\Page {
		if($this->outdated($this->url))
			return $this->pages->add($this->url, $this->origin->send());
		$content = $this->database->fetchSingle(
			'SELECT content
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			$this->url
		);
		return new Subscribing\ConstantPage(
			new Subscribing\HtmlWebPage(
				new FakeResponse($content),
				$this->origin
			),
			$content
		);
	}

	/**
	 * Is the url outdated and needs to be loaded from the another source
	 * By the source is meant the internet or probably another storage
	 * @param string $url
	 * @return bool
	 */
	private function outdated(string $url): bool {
		if(!$this->exists($url))
			return true;
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM pages
			WHERE (
				SELECT MAX(visited_at)
				FROM page_visits
				WHERE page_url IS NOT DISTINCT FROM ?
		  	) + INTERVAL "1 MINUTE" * ? < NOW()',
			$url,
			(new \DateInterval(self::EXPIRATION))->i
		);
	}

	/**
	 * Does the url exist in the database and therefore it is not the first access?
	 * @param string $url
	 * @return bool
	 */
	private function exists(string $url): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			$url
		);
	}
}