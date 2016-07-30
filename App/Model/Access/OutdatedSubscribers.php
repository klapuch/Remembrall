<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Security, Subscribing
};

/**
 * Subscribers without actual subscribing
 */
final class OutdatedSubscribers implements Subscribers {
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
				INNER JOIN subscriptions ON subscriptions.subscriber_id = subscribers.id
				INNER JOIN parts ON parts.id = subscriptions.part_id
				INNER JOIN (
					SELECT MAX(visited_at) AS visited_at, part_id
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON parts.id = part_visits.part_id
				WHERE visited_at + INTERVAL "1 MINUTE" * CAST(SUBSTRING(interval FROM "[0-9]+") AS INT) < NOW()
				AND page_url = ?
				AND expression = ?',
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