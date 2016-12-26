<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri
};
use Nette\Caching\Storages;

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

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->origin->add($part, $uri, $expression);
	}

	public function iterate(): \Iterator {
		$rows = (new Storage\ParameterizedQuery(
			$this->database,
			"SELECT page_url AS url, expression, parts.id, content, snapshot
				FROM parts
				RIGHT JOIN (
					SELECT MIN(SUBSTRING(interval FROM '[0-9]+')::INT) AS interval,
					part_id
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id 
				LEFT JOIN (
					SELECT MAX(visited_at) AS visited_at, part_id
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON part_visits.part_id = parts.id
				WHERE visited_at + INTERVAL '1 SECOND' * interval < NOW()
				ORDER BY visited_at ASC"
		))->rows();
		foreach($rows as $row) {
			$url = new Uri\ReachableUrl(new Uri\ValidUrl($row['url']));
			$page = new CachedPage(
				new FrugalPage(
					$url,
					new PostgresPage(
						new HtmlWebPage(new Http\BasicRequest('GET', $url)),
						$url,
						$this->database
					),
					$this->database
				),
				new Storages\MemoryStorage()
			);
			yield new ConstantPart(
				new PostgresPart(
					new HtmlPart(
						new MatchingExpression(
							new XPathExpression($page, $row['expression'])
						),
						$page
					),
					$row['id'],
					$this->database
				),
				$row['content'],
				$row['snapshot']
			);
		}
	}
}