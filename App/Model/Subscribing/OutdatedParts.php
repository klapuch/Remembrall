<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri, Http
};

/**
 * All the parts which are no longer trusted as reliable and need to be reloaded
 */
final class OutdatedParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, Storage\Database $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->origin->add($part, $uri, $expression);
	}

	public function iterate(): \Iterator {
		$rows = $this->database->fetchAll(
			"SELECT page_url AS url, expression, parts.id
				FROM parts
				LEFT JOIN (
					SELECT MIN(SUBSTRING(interval FROM '[0-9]+')::INT) AS interval,
					part_id, MIN(last_update) AS last_update
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id 
				WHERE last_update + INTERVAL '1 MINUTE' * INTERVAL < NOW()
				ORDER BY last_update ASC"
		);
		foreach($rows as $row) {
			$url = new Uri\ReachableUrl(new Uri\ValidUrl($row['url']));
			$page = new CachedPage(
				$url,
				new PostgresPage(
					new HtmlWebPage(new Http\BasicRequest('GET', $url)),
					$url,
					$this->database
				),
				$this->database
			);
			yield new PostgresPart(
				new HtmlPart(
					new MatchingExpression(
						new XPathExpression($page, $row['expression'])
					),
					$page
				),
				$row['id'],
				$this->database
			);
		}
	}
}