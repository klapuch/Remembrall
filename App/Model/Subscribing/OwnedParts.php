<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Storage
};

/**
 * Parts which are owned by the given subscriber
 */
final class OwnedParts implements Parts {
	private $database;
	private $myself;
	private $origin;

	public function __construct(
		Dibi\Connection $database,
		Access\Subscriber $myself,
		Parts $origin
	) {
		$this->database = $database;
		$this->myself = $myself;
		$this->origin = $origin;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		try {
			(new Storage\Transaction($this->database))->start(
				function() use ($interval, $part) {
					$this->database->query(
						'INSERT INTO parts
						(`interval`, page_id, expression, content, subscriber_id) VALUES
						(?, (SELECT ID FROM pages WHERE url = ?), ?, ?, ?)',
						sprintf('PT%dM', $interval->step()->i),
						$part->source()->url(),
						(string)$part->expression(),
						$part->content(),
						$this->myself->id()
					);
					$this->database->query(
						'INSERT INTO part_visits (part_id, visited_at) VALUES (?, ?)',
						$this->database->insertId(),
						$interval->start()
					);
				}
			);
			return $part;
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					(string)$part->expression(),
					$part->source()->url()
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function replace(Part $old, Part $new) {
		if(!$this->owned($old))
			throw new Exception\ExistenceException('You do not own this part');
		$this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		$this->database->query(
			'DELETE FROM parts
			WHERE subscriber_id = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)
			AND expression = ?',
			$this->myself->id(),
			$part->source()->url(),
			(string)$part->expression()
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression, url,
				pages.content AS page_content, `interval`, visited_at
				FROM parts
				INNER JOIN part_visits ON part_visits.part_id = parts.ID  
				LEFT JOIN pages ON pages.ID = parts.page_id
				WHERE subscriber_id = ?',
				$this->myself->id()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new ConstantPage($row['url'], $row['page_content']),
					$row['part_content'],
					new XPathExpression(
						new ConstantPage($row['url'], $row['page_content']),
						$row['expression']
					),
					$this->myself,
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
	 * Checks whether the subscriber really owns the given part
	 * @param Part $part
	 * @return bool
	 */
	private function owned(Part $part): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Part $ownedPart) use ($part) {
				return $part->equals($ownedPart);
			}
		);
	}
}
