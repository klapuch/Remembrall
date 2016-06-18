<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

final class ExpiredPostgresPages implements Pages {
	private $origin;
	private $database;
	private $expiration;

	public function __construct(
		Pages $origin,
		Dibi\Connection $database,
		Interval $expiration
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->expiration = $expiration;
	}

	public function add(Page $page) {
		$this->origin->add($page);
	}

	public function iterate(): array {
		return array_reduce(
			$this->database->fetchAll(
				'SELECT url, content
				FROM pages
				LEFT JOIN page_visits ON page_visits.page_id = pages.ID
				WHERE visited_at IS NULL
				OR visited_at + INTERVAL ? MINUTE <= ?',
				$this->expiration->step()->i,
				$this->expiration->start()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPage($row['url'], $row['content']);
				return $previous;
			}
		);
	}
}