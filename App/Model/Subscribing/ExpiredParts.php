<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\Access;

/**
 * Expired parts
 */
final class ExpiredParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, Dibi\Connection $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		return $this->origin->subscribe($part, $interval);
	}

	public function replace(Part $old, Part $new): Part {
		if(!$this->expired($old)) {
			throw new Exception\NotFoundException(
				'This part has not expired yet'
			);
		}
		return $this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		$this->origin->remove($part);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content, expression, visited_at, `interval`,
				pages.content AS page_content, pages.url 
				FROM parts
				INNER JOIN pages ON pages.ID = parts.page_id
				LEFT JOIN part_visits ON part_visits.part_id = parts.ID
				WHERE visited_at IS NULL
				OR visited_at + INTERVAL CAST(SUBSTR(`interval`, 3) AS UNSIGNED) MINUTE <= NOW()'
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new ConstantPage($row['url'], $row['page_content']),
					$row['content'],
					new XPathExpression(
						new ConstantPage($row['url'], $row['page_content']),
						$row['expression']
					),
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
