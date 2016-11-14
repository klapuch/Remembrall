<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri
};

/**
 * Frugal page without unnecessary requests to the origin source
 */
final class FrugalPage implements Page {
	private const EXPIRATION = 'PT10M';
	private $uri;
	private $origin;
	private $database;

	public function __construct(
		Uri\Uri $uri,
		Page $origin,
		Storage\Database $database
	) {
		$this->uri = $uri;
		$this->origin = $origin;
		$this->database = $database;
	}

	public function content(): \DOMDocument {
		if(!$this->exists($this->uri))
			return $this->origin->content();
		elseif($this->outdated($this->uri))
			return $this->refresh()->content();
		$dom = new DOM();
		$dom->loadHTML(
			$this->database->fetchColumn(
				'SELECT content
				FROM pages
				WHERE url IS NOT DISTINCT FROM ?',
				[$this->uri->reference()]
			)
		);
		return $dom;
	}

	public function refresh(): Page {
		return $this->origin->refresh();
	}

	/**
	 * Is the url outdated and needs to be loaded from the original source?
	 * @param Uri\Uri $uri
	 * @return bool
	 */
	private function outdated(Uri\Uri $uri): bool {
		return (bool)$this->database->fetchColumn(
			"SELECT 1
			FROM pages
			WHERE (
				SELECT MAX(visited_at)
				FROM page_visits
				WHERE page_url IS NOT DISTINCT FROM ?
			) + INTERVAL '1 MINUTE' * ? < NOW()",
			[$uri->reference(), (new \DateInterval(self::EXPIRATION))->i]
		);
	}

	/**
	 * Does the url exist in the database?
	 * @param Uri\Uri $uri
	 * @return bool
	 */
	private function exists(Uri\Uri $uri): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM pages
			WHERE url IS NOT DISTINCT FROM ?',
			[$uri->reference()]
		);
	}
}