<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

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

	public function add(Part $part, Uri\Uri $url, string $expression, string $language): void {
		$this->origin->add($part, $url, $expression, $language);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT id, page_url AS url, (expression).value AS expression,
				content, snapshot, (expression).language, occurrences
				FROM parts
				INNER JOIN counted_subscriptions() AS subscriptions ON subscriptions.part_id = parts.id
				ORDER BY occurrences DESC'
			)
		))->rows();
		foreach ($parts as $part) {
			yield new StoredPart(
				new FakePart(),
				$part['id'],
				new Storage\MemoryPDO($this->database, $part)
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