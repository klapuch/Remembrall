<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Time, Storage
};

final class PostgresSubscription implements Subscription {
	private $id;
	private $database;

	public function __construct(int $id, Storage\Database $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function cancel(): void {
		$this->database->query(
			'DELETE FROM subscriptions
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		);
	}

	public function edit(Time\Interval $interval): void {
		$this->database->query(
			'UPDATE subscriptions
			SET interval = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$interval->iso(), $this->id]
		);
	}

	public function notify(): void {
		(new Storage\PostgresTransaction($this->database))->start(function() {
			$this->database->query(
				'INSERT INTO notifications (subscription_id, notified_at) VALUES
				(?, NOW())',
				[$this->id]
			);
			$this->database->query(
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
				[':id' => $this->id]
			);
		});
	}
}