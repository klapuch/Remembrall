<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

/**
 * Cached page on the database side
 */
final class CachedPage implements Page {
	const EXPIRATION = 'PT10M';
	private $url;
	private $origin;
	private $database;

	public function __construct(
		string $url,
		Page $origin,
		Storage\Database $database
	) {
		$this->url = $url;
		$this->origin = $origin;
		$this->database = $database;
	}

	public function content(): \DOMDocument {
        if($this->outdated($this->url))
        	return $this->refresh()->content();
		$dom = new DOM();
		$dom->loadHTML(
			$this->database->fetchColumn(
				'SELECT content
				FROM pages
				WHERE url IS NOT DISTINCT FROM ?',
				[$this->url]
			)
		);
		return $dom;
	}

	public function refresh(): Page {
		return $this->origin->refresh();
	}

	/**
	 * Is the url outdated and needs to be loaded from the another source?
	 * By the source is meant the internet or probably another storage
	 * @param string $url
	 * @return bool
	 */
	private function outdated(string $url): bool {
		if(!$this->exists($url))
			return true;
		return (bool)$this->database->fetchColumn(
			"SELECT 1
			FROM pages
			WHERE (
				SELECT MAX(visited_at)
				FROM page_visits
				WHERE page_url IS NOT DISTINCT FROM ?
			) + INTERVAL '1 MINUTE' * ? < NOW()",
			[$url, (new \DateInterval(self::EXPIRATION))->i]
		);
	}

	/**
	 * Does the url exist in the database and therefore it's not the first access?
	 * @param string $url
	 * @return bool
	 */
	private function exists(string $url): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			[$url]
		);
	}
}
