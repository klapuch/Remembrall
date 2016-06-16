<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Nette\Security;
use Remembrall\Exception;

final class OwnedPostgresParts implements Parts {
	private $database;
	private $page;
	private $myself;

	public function __construct(
		Dibi\Connection $database,
		Page $page,
		Security\IIdentity $myself
	) {
		$this->database = $database;
		$this->page = $page;
		$this->myself = $myself;
	}

	public function subscribe(Part $part, Interval $interval) {
		try {
			$this->database->query(
				'INSERT INTO parts
				(url, expression, content, visited_at, `interval`, subscriber_id) VALUES
				(?, ?, ?, ?, ?, ?)',
				$this->page->url(),
				(string)$part->expression(),
				$part->content(),
				$interval->start(),
				$interval->step()->i,
				$this->myself->getId()
			);
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					(string)$part->expression(),
					$this->page->url()
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT content, expression
				FROM parts
				WHERE url = ? AND subscriber_id = ?',
				$this->page->url(),
				$this->myself->getId()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					$this->page,
					$row['content'],
					new XPathExpression($this->page, $row['expression'])
				);
				return $previous;
			}
		);
	}
}
