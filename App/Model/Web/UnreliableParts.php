<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Http;
use Klapuch\Storage;
use Klapuch\Uri;

/**
 * All the parts which are no longer trusted as reliable and need to be reloaded
 */
final class UnreliableParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression, string $language): void {
		$this->origin->add($part, $uri, $expression, $language);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				"SELECT page_url AS url, (expression).value AS expression,
				parts.id, content, snapshot, (expression).language,
				occurrences
				FROM parts
				JOIN counted_subscriptions() AS subscriptions ON subscriptions.part_id = parts.id 
				JOIN (
					SELECT MIN(interval_seconds) AS interval, part_id
					FROM readable_subscriptions()
					GROUP BY part_id
				) AS readable_subscriptions ON readable_subscriptions.part_id = parts.id 
				JOIN (
					SELECT MAX(visited_at) AS visited_at, part_id
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON part_visits.part_id = parts.id
				WHERE visited_at + INTERVAL '1 SECOND' * interval < NOW()
				ORDER BY visited_at ASC"
			)
		))->rows();
		foreach ($parts as $part) {
			$url = new Uri\ValidUrl($part['url']);
			$page = new FrugalPage(
				$url,
				new StoredPage(
					new HtmlWebPage(
						new Http\BasicRequest('GET', new Uri\ReachableUrl($url))
					),
					$url,
					$this->database
				),
				$this->database
			);
			yield new StoredPart(
				new HtmlPart(
					new MatchingExpression(
						new SuitableExpression(
							$part['language'],
							$page,
							$part['expression']
						)
					),
					$page
				),
				$part['id'],
				new Storage\MemoryPDO($this->database, $part)
			);
		}
	}

	public function count(): int {
		return (new Storage\ParameterizedQuery(
			$this->database,
			"SELECT COUNT(*)
			FROM parts
			RIGHT JOIN (
				SELECT MIN(interval_seconds) AS interval,
				part_id
				FROM readable_subscriptions()
				GROUP BY part_id
			) AS subscriptions ON subscriptions.part_id = parts.id 
			LEFT JOIN (
				SELECT MAX(visited_at) AS visited_at, part_id
				FROM part_visits
				GROUP BY part_id
			) AS part_visits ON part_visits.part_id = parts.id
			WHERE visited_at + INTERVAL '1 SECOND' * interval < NOW()"
		))->field();
	}
}