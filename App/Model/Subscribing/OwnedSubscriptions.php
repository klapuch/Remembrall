<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri, Time, Output
};
use Remembrall\Model\Access;
use Remembrall\Exception\DuplicateException;

/**
 * All the subscriptions owned by one particular subscriber
 */
final class OwnedSubscriptions implements Subscriptions {
	private const EMPTY_FORMAT = [];
	private $owner;
	private $database;

	public function __construct(
		Access\Subscriber $owner,
		Storage\Database $database
	) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function print(Output\Format $format): array {
        $rows = $this->database->fetchAll(
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
        );
        return array_reduce(
            array_map(
                function(array $row) use($format) {
                    return $format->with('expression', $row['expression'])
                        ->with('url', $row['url'])
                        ->with(
                            'interval',
                            new Time\TimeInterval(
                                new \DateTimeImmutable($row['visited_at']),
                                new \DateInterval($row['interval'])
                            )
                        )
                        ->with('lastUpdate', $row['last_update']);
                },
                $rows
            ), function($formats, $format) {
                $formats[] = $format;
            return $formats;
        }, self::EMPTY_FORMAT);
	}

	public function subscribe(
		Uri\Uri $url,
		string $expression,
		Time\Interval $interval
	): void {
		try {
			$this->database->query(
				'INSERT INTO subscriptions
				(part_id, subscriber_id, interval, last_update, snapshot)
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
					$url->reference()
				]
			);
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
}