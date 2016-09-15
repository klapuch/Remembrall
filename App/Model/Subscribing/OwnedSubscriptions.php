<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri, Time
};
use Remembrall\Model\Access;
use Remembrall\Exception\DuplicateException;

final class OwnedSubscriptions implements Subscriptions {
	private $owner;
	private $database;

	public function __construct(
		Access\Subscriber $owner,
		Storage\Database $database
	) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT expression, page_url AS url, interval, visited_at, last_update
				FROM parts
				INNER JOIN (
					SELECT part_id, MAX(visited_at) AS visited_at
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON parts.id = part_visits.part_id
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				WHERE subscriptions.subscriber_id IS NOT DISTINCT FROM ?
				ORDER BY visited_at DESC',
				[$this->owner->id()]
			),
			function($subscriptions, array $row) {
				$subscriptions[] = new ConstantSubscription(
					new OwnedSubscription(
                        new Uri\ValidUrl($row['url']),
						$row['expression'],
						$this->owner,
						$this->database
					),
					new Time\TimeInterval(
						new \DateTimeImmutable((string)$row['visited_at']),
						new \DateInterval($row['interval'])
					),
					new \DateTimeImmutable($row['last_update'])
				);
				return $subscriptions;
			}
		);
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	) {
		try {
			$this->database->query(
				'INSERT INTO subscriptions
				(part_id, subscriber_id, interval, last_update)
				(
					SELECT id, ?, ?, NOW()
					FROM parts
					WHERE expression IS NOT DISTINCT FROM ?
					AND page_url IS NOT DISTINCT FROM ?
				)',
				[
					$this->owner->id(),
					$interval->iso(),
					$expression,
					$uri->reference()
				]
			);
		} catch(Storage\UniqueConstraint $ex) {
			throw new DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					$expression,
					$uri->reference()
				),
				$ex->getCode(),
				$ex
			);
		}
	}
}
