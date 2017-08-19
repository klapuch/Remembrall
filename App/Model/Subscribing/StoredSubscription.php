<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Time;

/**
 * Subscription stored in the database
 */
final class StoredSubscription implements Subscription {
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
			WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function edit(Time\Interval $interval): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE subscriptions
			SET interval = ?
			WHERE id = ?',
			[$interval->iso(), $this->id]
		))->execute();
	}

	public function notify(): void {
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
			WHERE id = :id',
			['id' => $this->id]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$subscription = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT subscriptions.id, interval, page_url AS url, (expression).value AS expression, (expression).language,
			content, to_ISO8601(last_update) AS last_update, to_ISO8601(visited_at) AS visited_at
			FROM readable_subscriptions() AS subscriptions
			LEFT JOIN parts ON subscriptions.part_id = parts.id
			INNER JOIN part_visits ON part_visits.part_id = parts.id
			WHERE subscriptions.id = ?',
			[$this->id]
		))->row();
		return new Output\FilledFormat($format, $subscription);
	}
}