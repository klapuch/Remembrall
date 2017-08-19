<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;

/**
 * All the subscriptions owned by one particular subscriber
 */
final class OwnedSubscriptions implements Subscriptions {
	private $owner;
	private $database;

	public function __construct(Access\User $owner, \PDO $database) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function subscribe(
		Uri\Uri $url,
		string $expression,
		string $language,
		Time\Interval $interval
	): void {
		try {
			(new Storage\ParameterizedQuery(
				$this->database,
				'INSERT INTO subscriptions
				(part_id, user_id, interval, last_update, snapshot)
				(
					SELECT id, ?, ?, NOW(), snapshot
					FROM parts
					WHERE expression = ROW(?, ?)::expression
					AND page_url = ?
				)',
				[
					$this->owner->id(),
					$interval->iso(),
					$expression,
					$language,
					$url->reference(),
				]
			))->execute();
		} catch (\Klapuch\Storage\UniqueConstraint $ex) {
			throw new \UnexpectedValueException(
				sprintf(
					'"%s" expression on "%s" page is already subscribed by you',
					$expression,
					$url->reference()
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				'SELECT subscriptions.id, (expression).value AS expression, page_url AS url, interval, (expression).language,
				to_ISO8601(visited_at) AS visited_at, to_ISO8601(last_update) AS last_update, content
				FROM parts
				LEFT JOIN (
					SELECT part_id, MAX(visited_at) AS visited_at
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON parts.id = part_visits.part_id
				INNER JOIN readable_subscriptions() AS subscriptions ON subscriptions.part_id = parts.id
				WHERE subscriptions.user_id = ?
				ORDER BY visited_at DESC'
			),
			$selection->criteria([$this->owner->id()])
		))->rows();
		foreach ($subscriptions as $subscription) {
			yield new StoredSubscription(
				$subscription['id'],
				new Storage\MemoryPDO($this->database, $subscription)
			);
		}
	}
}