<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

/**
 * All parts stored in the database
 */
final class CollectiveMySqlParts implements Parts {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function subscribe(Part $part, Interval $interval) {
		try {
			$this->database->begin();
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
				(page_id, expression, content, `interval`, subscriber_id)
				SELECT (SELECT ID FROM pages WHERE url = ?), ?, ?, ?, ID
				FROM subscribers',
				$part->source()->url(),
				(string)$part->expression(),
				$part->content(),
				$interval->step()->i
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
			$this->database->commit();
		} catch(\Exception $ex) {
			$this->database->rollback();
			throw new Dibi\Exception(
				'An error occurred during subscribing a new part',
				$ex->getCode(),
				$ex
			);
		} finally {
			$this->database->query('SET autocommit = 1');
			$this->database->query('UNLOCK TABLES');
		}
	}

	public function replace(Part $old, Part $new) {
		$this->database->query(
			'UPDATE parts SET content = ?
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			$new->content(),
			$old->owner()->id(),
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
				pages.content AS page_content, expression, subscriber_id
				FROM parts
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
					new MySqlSubscriber($row['subscriber_id'], $this->database)
				);
				return $previous;
			}
		);
	}
}
