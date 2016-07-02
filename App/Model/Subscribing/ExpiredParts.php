<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\Access;

/**
 * Expired parts on the given page
 */
final class ExpiredParts implements Parts {
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

	public function subscribe(Part $part, Interval $interval): Part {
		return $this->origin->subscribe($part, $interval);
	}

	public function replace(Part $old, Part $new) {
		if(!$this->expired($old)) {
			throw new Exception\ExistenceException(
				'This part has not expired yet'
			);
		}
		$this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		$this->origin->remove($part);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content, expression, parts.subscriber_id, visited_at,
 				`interval`
				FROM parts
				INNER JOIN pages ON pages.ID = parts.page_id
				LEFT JOIN part_visits ON part_visits.part_id = parts.ID
				WHERE url = ?
				AND visited_at IS NULL
				OR visited_at + INTERVAL CAST(SUBSTR(`interval`, 3) AS UNSIGNED) MINUTE <= NOW()',
				[$this->page->url()]
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					$this->page,
					$row['content'],
					new XPathExpression($this->page, $row['expression']),
					new Access\MySqlSubscriber($row['subscriber_id'], $this->database),
					new DateTimeInterval(
						new \DateTimeImmutable((string)$row['visited_at']),
						new \DateInterval($row['interval'])
					)
				);
				return $previous;
			}
		);
	}

	/**
	 * Checks whether the given part is really expired
	 * @param Part $part
	 * @return bool
	 */
	private function expired(Part $part): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Part $expiredPart) use ($part) {
				return $part->equals($expiredPart);
			}
		);
	}
}
