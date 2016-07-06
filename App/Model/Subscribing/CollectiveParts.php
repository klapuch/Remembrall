<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Storage
};

/**
 * All parts stored in the database - shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;
	private $myself;

	public function __construct(
		Dibi\Connection $database,
		Access\Subscriber $myself
	) {
		$this->database = $database;
		$this->myself = $myself;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		try {
			(new Storage\Transaction($this->database))->start(
				function() use ($part, $interval) {
					$this->database->query('SET autocommit = 0');
					$this->database->query(
						'LOCK TABLES parts WRITE,
						pages WRITE,
						subscribers WRITE,
						part_visits WRITE'
					);
					$firstId = $this->database->fetchSingle(
						'SELECT ID + 1 FROM parts ORDER BY ID DESC LIMIT 1'
					);
					$this->database->query(
						'INSERT INTO parts
						(`interval`, page_id, expression, content, subscriber_id)
						SELECT ?, (SELECT ID FROM pages WHERE url = ?), ?, ?, ID
						FROM subscribers',
						sprintf('PT%dM', $interval->step()->i),
						$part->source()->url(),
						(string)$part->expression(),
						$part->content()
					);
					$lastId = $this->database->fetchSingle(
						'SELECT ID FROM parts ORDER BY ID DESC LIMIT 1'
					);
					$this->database->query(
						'INSERT INTO part_visits (part_id, visited_at)
						SELECT ID, ? FROM parts WHERE ID IN %in',
						$interval->start(),
						range($firstId, $lastId)
					);
				}
			);
			return $part;
		} finally {
			$this->database->query('SET autocommit = 1');
			$this->database->query('UNLOCK TABLES');
		}
	}

	public function replace(Part $old, Part $new) {
		$this->database->query(
			'UPDATE parts
			INNER JOIN part_visits ON part_visits.part_id = parts.ID
			SET content = ?, visited_at = NOW()
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			$new->content(),
			$this->myself->id(),
			(string)$old->expression(),
			$old->source()->url()
		);
	}

	public function remove(Part $part) {
		$this->database->query(
			'DELETE FROM parts
			WHERE expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			(string)$part->expression(),
			$part->source()->url()
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, url,
				pages.content AS page_content, expression,
				`interval`, visited_at
				FROM parts
				INNER JOIN part_visits ON part_visits.part_id = parts.ID 
				LEFT JOIN pages ON pages.ID = parts.page_id'
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new ConstantPage($row['url'], $row['page_content']),
					$row['part_content'],
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
}
