<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri, Output
};

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

	public function getIterator(): \Iterator {
		foreach($this->rows() as $part) {
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
			yield new ConstantPart(
				new StoredPart(
					new HtmlPart(
						new MatchingExpression(
							new XPathExpression($page, $part['expression'])
						),
						$page
					),
					$part['id'],
					$this->database
				),
				$part['content'],
				$part['snapshot']
			);
		}
	}

	public function print(Output\Format $format): array {
		return array_map(
			function(array $part) use ($format): Output\Format {
				return $format->with('id', $part['id'])
					->with('url', $part['url'])
					->with('expression', $part['expression'])
					->with('content', $part['content']);
			},
			$this->rows()
		);
	}

	private function rows(): array {
		return (new Storage\ParameterizedQuery(
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
	}
}