<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri, Output, Dataset
};

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(:url, :expression, :content, :snapshot)
			ON CONFLICT (page_url, expression)
			DO UPDATE SET content = :content, snapshot = :snapshot',
			[
				'url' => $url->reference(),
				'expression' => $expression,
				'content' => $part->content(),
				'snapshot' => $part->snapshot(),
			]
		))->execute();
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT id, page_url AS url, content, expression, snapshot,
				COALESCE(occurrences, 0) AS occurrences
				FROM parts
				LEFT JOIN (
					SELECT part_id, COUNT(*) AS occurrences
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id
				ORDER BY id ASC'
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
}