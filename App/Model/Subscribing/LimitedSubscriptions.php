<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Storage, Time, Uri
};
use Remembrall\Model\Access;

/**
 * Disallow subscribing after more than X subscriptions
 */
final class LimitedSubscriptions implements Subscriptions {
	private const LIMIT = 5;
	private $origin;
	private $subscriber;
	private $database;

	public function __construct(
		Subscriptions $origin,
		Access\Subscriber $subscriber,
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->subscriber = $subscriber;
		$this->database = $database;
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void {
		if($this->overstepped()) {
			throw new \OverflowException(
				sprintf(
					'You have reached the limit of %d subscribed parts',
					self::LIMIT
				)
			);
		}
		$this->origin->subscribe($uri, $expression, $interval);
	}

	public function iterate(): \Iterator {
		return $this->origin->iterate();
	}

	/**
	 * Has the subscriber subscribed more than X parts and overstepped the limit?
	 * @return bool
	 */
	private function overstepped(): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id 
			WHERE subscriber_id IS NOT DISTINCT FROM ?
			HAVING COUNT(parts.id) >= ?',
			[$this->subscriber->id(), self::LIMIT]
		);
	}

	public function print(Output\Format $format): array {
		return $this->origin->print($format);
	}
}