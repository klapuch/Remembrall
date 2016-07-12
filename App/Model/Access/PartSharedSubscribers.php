<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Security, Subscribing
};

/**
 * Subscribers who subscribing shared (same) part
 */
final class PartSharedSubscribers implements Subscribers {
	private $origin;
	private $part;
	private $database;

	public function __construct(
		Subscribers $origin,
		Subscribing\Part $part,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->part = $part;
		$this->database = $database;
	}

	public function register(string $email, string $password): Subscriber {
		return $this->origin->register($email, $password);
	}

	public function iterate(): array {
		return array_reduce(
			$this->database->fetchAll(
				'SELECT subscribers.ID, email
				FROM subscribers
				INNER JOIN parts ON parts.subscriber_id = subscribers.ID
				WHERE page_id = (SELECT ID FROM pages WHERE url = ?)
				AND expression = ?',
				$this->part->source()->url(),
				(string)$this->part->expression()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantSubscriber($row['ID'], $row['email']);
				return $previous;
			}
		);
	}
}