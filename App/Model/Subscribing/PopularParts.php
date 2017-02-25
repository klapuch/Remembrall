<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Uri;

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

	public function iterate(Dataset\Selection $selection): \Traversable {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT id, page_url AS url, expression, content, snapshot,
				occurrences
				FROM parts
				INNER JOIN (
					SELECT part_id, COUNT(*) AS occurrences
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id
				ORDER BY occurrences DESC'
			)
		))->rows();
		foreach($parts as $part) {
			yield new ConstantPart(
				new FakePart(),
				$part['content'],
				$part['snapshot'],
				$part
			);
		}
	}

	public function count(): int {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT COUNT(*) FROM parts'
		))->field();
	}
}