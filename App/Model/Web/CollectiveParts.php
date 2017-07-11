<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Uri;

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression, string $language): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(:url, ROW(:expression, :language), :content, :snapshot)
			ON CONFLICT (page_url, expression)
			DO UPDATE SET content = :content, snapshot = :snapshot',
			[
				'url' => $url->reference(),
				'expression' => $expression,
				'language' => $language,
				'content' => $part->content(),
				'snapshot' => $part->snapshot(),
			]
		))->execute();
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT id, page_url AS url, content, (expression).value AS expression, snapshot,
				COALESCE(occurrences, 0) AS occurrences
				FROM parts
				LEFT JOIN counted_subscriptions() AS subscriptions ON subscriptions.part_id = parts.id
				ORDER BY id ASC'
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