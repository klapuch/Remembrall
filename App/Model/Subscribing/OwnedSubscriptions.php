<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Storage, Time, Uri
};
use Remembrall\Exception\DuplicateException;
use Remembrall\Model\Access;

/**
 * All the subscriptions owned by one particular subscriber
 */
final class OwnedSubscriptions implements Subscriptions {
	private const EMPTY_FORMAT = [];
	private $owner;
	private $database;

	public function __construct(Access\Subscriber $owner, \PDO $database) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function subscribe(
		Uri\Uri $url,
		string $expression,
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
					WHERE expression IS NOT DISTINCT FROM ?
					AND page_url IS NOT DISTINCT FROM ?
				)',
				[
					$this->owner->id(),
					$interval->iso(),
					$expression,
					$url->reference(),
				]
			))->execute();
		} catch(Storage\UniqueConstraint $ex) {
			throw new DuplicateException(
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

	public function iterate(): \Iterator {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id
			FROM subscriptions
			WHERE user_id IS NOT DISTINCT FROM ?',
			[$this->owner->id()]
		))->rows();
		foreach($subscriptions as ['id' => $id])
			yield new StoredSubscription($id, $this->database);
	}

	public function print(Output\Format $format): array {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT subscriptions.id, expression, page_url, interval,
			visited_at, last_update
			FROM parts
			INNER JOIN (
				SELECT part_id, MAX(visited_at) AS visited_at
				FROM part_visits
				GROUP BY part_id
			) AS part_visits ON parts.id = part_visits.part_id
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id
			WHERE subscriptions.user_id IS NOT DISTINCT FROM ?
			ORDER BY visited_at DESC',
			[$this->owner->id()]
		))->rows();
		return array_map(
			function(array $subscription) use ($format): Output\Format {
				return $format->with('expression', $subscription['expression'])
					->with('id', $subscription['id'])
					->with('url', $subscription['page_url'])
					->with(
						'interval',
						(string)new Time\TimeInterval(
							new \DateTimeImmutable($subscription['visited_at']),
							new \DateInterval($subscription['interval'])
						)
					)
					->with('lastUpdate', $subscription['last_update']);
			},
			$subscriptions
		);
	}
}