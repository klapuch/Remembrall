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
	private $url;
	private $expression;
	private $database;

	public function __construct(
		Subscribers $origin,
		string $url,
		string $expression,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->url = $url;
		$this->expression = $expression;
		$this->database = $database;
	}

	public function register(string $email, string $password): Subscriber {
		return $this->origin->register($email, $password);
	}

	public function iterate(): array {
		return array_reduce(
			$this->database->fetchAll(
				'SELECT subscribers.id, email
				FROM subscribers
				INNER JOIN subscribed_parts ON subscribed_parts.subscriber_id = subscribers.id
				INNER JOIN parts ON parts.id = subscribed_parts.part_id 
				WHERE page_url = ? AND expression = ?',
				$this->url,
				$this->expression
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantSubscriber($row['id'], $row['email']);
				return $previous;
			}
		);
	}
}