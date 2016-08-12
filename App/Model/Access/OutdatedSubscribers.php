<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;

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
		Storage\Database $database
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
				"WITH updated AS (
					UPDATE subscriptions
					SET last_update = NOW()
					WHERE subscriber_id IN (
						SELECT subscribers.id
						FROM subscribers
						INNER JOIN subscriptions ON subscriptions.subscriber_id = subscribers.id
						INNER JOIN parts ON parts.id = subscriptions.part_id
						WHERE last_update + INTERVAL '1 MINUTE' * CAST(SUBSTRING(INTERVAL FROM '[0-9]+') AS INT) < NOW()
						AND page_url IS NOT DISTINCT FROM ?
						AND expression IS NOT DISTINCT FROM ?
					) RETURNING subscriber_id AS id
				) SELECT * FROM subscribers WHERE id IN (SELECT id FROM updated)",
				[$this->url, $this->expression]
			),
			function($previous, array $row) {
				$previous[] = new ConstantSubscriber($row['id'], $row['email']);
				return $previous;
			}
		);
	}
}