<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

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
		if($this->expired($page))
			return $this->origin->add($page);
		return $page;
	}

	public function replace(Page $old, Page $new) {
		if($this->expired($old))
			$this->origin->replace($old, $new);
	}

	public function iterate(): array {
		return (array)array_reduce(
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

	/**
	 * Checks whether the given page is really expired
	 * @param Page $page
	 * @return bool
	 */
	private function expired(Page $page): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Page $expiredPage) use ($page) {
				return $page->equals($expiredPage);
			}
		);
	}
}