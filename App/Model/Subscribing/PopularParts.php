<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri, Output
};

/**
 * The most subscribed and therefore the most popular parts
 */
final class PopularParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression): void {
		$this->origin->add($part, $url, $expression);
	}

	public function getIterator(): \Iterator {
		return $this->origin->getIterator();
	}

	public function print(Output\Format $format): array {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url AS url, expression, content, occurrences
			FROM parts
			INNER JOIN (
				SELECT part_id, COUNT(*) AS occurrences
				FROM subscriptions
				GROUP BY part_id
			) AS subscriptions ON subscriptions.part_id = parts.id
			ORDER BY occurrences DESC'
		))->rows();
		return array_map(
			function(array $part) use ($format): Output\Format {
				return $format->with('id', $part['id'])
					->with('url', $part['url'])
					->with('expression', $part['expression'])
					->with('content', $part['content'])
					->with('occurrences', $part['occurrences']);
			},
			$parts
		);
	}
}