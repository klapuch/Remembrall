<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;

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
				'WITH updated AS (
					UPDATE subscriptions
					SET hash = (
						SELECT content_hash
						FROM parts
						WHERE page_url = ?
						AND expression = ?
					) WHERE subscriber_id IN (
						SELECT subscribers.id
						FROM subscribers
						INNER JOIN subscriptions ON subscriptions.subscriber_id = subscribers.id
						INNER JOIN parts ON parts.id = subscriptions.part_id
						INNER JOIN (
							SELECT MAX(visited_at) AS visited_at, part_id
							FROM part_visits
							GROUP BY part_id
						) AS part_visits ON parts.id = part_visits.part_id
						WHERE visited_at + INTERVAL "1 MINUTE" * CAST(SUBSTRING(INTERVAL FROM "[0-9]+") AS INT) < NOW()
						AND page_url = ?
						AND expression = ?
					) RETURNING subscriber_id AS id
				) SELECT * FROM subscribers WHERE id IN (SELECT id FROM updated)',
				$this->url,//todo
				$this->expression,
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