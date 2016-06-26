<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

/**
 * Expired pages in range of the given interval
 */
final class ExpiredPages implements Pages {
	private $origin;
	private $database;
	private $interval;

	public function __construct(
		Pages $origin,
		Dibi\Connection $database,
		Interval $interval
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->interval = $interval;
	}

	public function add(Page $page): Page {
		return $this->origin->add($page);
	}

	public function iterate(): array {
		return array_reduce(
			$this->database->fetchAll(
				'SELECT url, content
				FROM pages
				LEFT JOIN page_visits ON page_visits.page_id = pages.ID
				WHERE visited_at IS NULL
				OR visited_at + INTERVAL ? MINUTE <= ?',
				$this->interval->step()->i,
				$this->interval->start()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPage($row['url'], $row['content']);
				return $previous;
			}
		);
	}
}