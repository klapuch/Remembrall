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
				INNER JOIN subscribed_parts ON subscribed_parts.subscriber_id = subscribers.ID
				INNER JOIN parts ON parts.ID = subscribed_parts.part_id 
				WHERE page_url = ? AND expression = ?',
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