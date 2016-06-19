<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

final class OwnedMySqlParts implements Parts {
	private $database;
	private $page;
	private $myself;

	public function __construct(
		Dibi\Connection $database,
		Page $page,
		Subscriber $myself
	) {
		$this->database = $database;
		$this->page = $page;
		$this->myself = $myself;
	}

	public function subscribe(Part $part, Interval $interval) {
		try {
			$this->database->begin();
			$this->database->query(
				'INSERT INTO parts
				(page_id, expression, content, `interval`, subscriber_id) VALUES
				((SELECT ID FROM pages WHERE url = ?), ?, ?, ?, ?)',
				$this->page->url(),
				(string)$part->expression(),
				$part->content(),
				$interval->step()->i,
				$this->myself->id()
			);
			$this->database->query(
				'INSERT INTO part_visits (part_id, visited_at) VALUES (?, ?)',
				$this->database->insertId(),
				new \DateTimeImmutable()
			);
			$this->database->commit();
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			$this->database->rollback();
			throw new Exception\DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					(string)$part->expression(),
					$this->page->url()
				),
				$ex->getCode(),
				$ex
			);
		} catch(\Exception $ex) {
			$this->database->rollback();
			throw new Dibi\Exception(
				'An error occurred during subscribing a new part',
				$ex->getCode(),
				$ex
			);
		}
	}

	public function replace(Part $old, Part $new) {
		if(!$this->owned($old))
			throw new Exception\ExistenceException('You do not own this part');
		$this->database->query(
			'UPDATE parts SET content = ?
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			$new->content(),
			$this->myself->id(),
			(string)$old->expression(),
			$this->page->url()
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content, expression
				FROM parts
				INNER JOIN pages ON pages.ID = parts.page_id
				WHERE url = ? AND subscriber_id = ?',
				$this->page->url(),
				$this->myself->id()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					$this->page,
					$row['content'],
					new XPathExpression($this->page, $row['expression']),
					$this->myself
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
