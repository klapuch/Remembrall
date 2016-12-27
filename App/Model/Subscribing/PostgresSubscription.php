<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Time
};

/**
 * Subscription stored in the Postgres database
 */
final class PostgresSubscription implements Subscription {
	private $id;
	private $database;

	public function __construct(int $id, \PDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function cancel(): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM subscriptions
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		))->execute();
	}

	public function edit(Time\Interval $interval): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE subscriptions
			SET interval = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$interval->iso(), $this->id]
		))->execute();
	}

	public function notify(): void {
		(new Storage\Transaction($this->database))->start(function() {
			(new Storage\ParameterizedQuery(
				$this->database,
				'INSERT INTO notifications (subscription_id, notified_at) VALUES
				(?, NOW())',
				[$this->id]
			))->execute();
			(new Storage\ParameterizedQuery(
				$this->database,
				'UPDATE subscriptions
				SET snapshot = (
					SELECT snapshot
					FROM parts
					WHERE id = (
						SELECT part_id
						FROM subscriptions
						WHERE id = :id
					)
				)
				WHERE id IS NOT DISTINCT FROM :id',
				['id' => $this->id]
			))->execute();
		});
	}
}