<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

final class CollectiveMySqlParts implements Parts {
	private $database;
	private $page;

	public function __construct(Dibi\Connection $database, Page $page) {
		$this->database = $database;
		$this->page = $page;
	}

	public function subscribe(Part $part, Interval $interval) {
		try {
			$this->database->begin();
			$firstId = $this->database->fetchSingle( //TODO: LOCK
				'SELECT ID + 1 FROM parts ORDER BY ID DESC LIMIT 1'
			);
			$this->database->query(
				'INSERT INTO parts
				(page_id, expression, content, `interval`, subscriber_id)
				SELECT (SELECT ID FROM pages WHERE url = ?), ?, ?, ?, ID
				FROM subscribers',
				$this->page->url(),
				(string)$part->expression(),
				$part->content(),
				$interval->step()->i
			);
			$lastId = $this->database->fetchSingle( //TODO: LOCK
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
				$ex->getMessage(),
				$ex->getCode(),
				$ex
			);
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

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content, expression, parts.subscriber_id
				FROM parts
				LEFT JOIN pages ON pages.ID = parts.page_id
				WHERE url = ?',
				$this->page->url()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					$this->page,
					$row['content'],
					new XPathExpression($this->page, $row['expression']),
					new MySqlSubscriber($row['subscriber_id'], $this->database)
				);
				return $previous;
			}
		);
	}
}
