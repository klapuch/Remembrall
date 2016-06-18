<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

final class ExpiredPostgresParts implements Parts {
	private $origin;
	private $page;
	private $database;

	public function __construct(
		Parts $origin,
		Page $page,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->page = $page;
		$this->database = $database;
	}

	public function subscribe(Part $part, Interval $interval) {
		$this->origin->subscribe($part, $interval);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content, expression
				FROM parts
				INNER JOIN pages ON pages.ID = parts.page_id
				LEFT JOIN part_visits ON part_visits.part_id = parts.ID
				WHERE url = ?
				AND visited_at IS NULL
				OR visited_at + INTERVAL `interval` MINUTE <= NOW()',
				[$this->page->url()]
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					$this->page,
					$row['content'],
					new XPathExpression($this->page, $row['expression'])
				);
				return $previous;
			}
		);
	}
}
