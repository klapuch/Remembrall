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
					WHERE expression IS NOT DISTINCT FROM ?
					AND language IS NOT DISTINCT FROM ?
					AND page_url IS NOT DISTINCT FROM ?
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
			throw new \Remembrall\Exception\DuplicateException(
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
				'SELECT subscriptions.id, expression, page_url AS url, interval, language,
				visited_at, last_update, content
				FROM parts
				LEFT JOIN (
					SELECT part_id, MAX(visited_at) AS visited_at
					FROM part_visits
					GROUP BY part_id
				) AS part_visits ON parts.id = part_visits.part_id
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				WHERE subscriptions.user_id IS NOT DISTINCT FROM ?
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