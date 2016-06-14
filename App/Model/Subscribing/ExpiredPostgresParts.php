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
				"SELECT content, expression
				FROM parts
				WHERE url = ? AND 
				visited_at + INTERVAL '`interval` MINUTES' >= NOW()",
				$this->page->url()
			),
			function($previous, array $row) {
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
