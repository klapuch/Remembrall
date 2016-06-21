<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

/**
 * Disallow subscribing more than X parts
 */
final class LimitedMySqlParts implements Parts {
	const LIMIT = 5;
	private $database;
	private $subscriber;
	private $origin;

	public function __construct(
		Dibi\Connection $database,
		Subscriber $subscriber,
		Parts $origin
	) {
		$this->database = $database;
		$this->subscriber = $subscriber;
		$this->origin = $origin;
	}

	public function subscribe(Part $part, Interval $interval) {
		if($this->overstepped()) {
			throw new \OverflowException(
				sprintf(
					'You have reached limit of %d subscribed parts',
					self::LIMIT
				)
			);
		}
		$this->origin->subscribe($part, $interval);
	}

	public function replace(Part $old, Part $new) {
		$this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		$this->origin->remove($part);
	}

	public function iterate(): array {
		return $this->origin->iterate();
	}

	/**
	 * Checks whether the subscriber has more than X subscribed parts
	 * @return bool
	 */
	private function overstepped(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM parts
			WHERE subscriber_id = ?
			HAVING COUNT(ID) >= ?',
			$this->subscriber->id(),
			self::LIMIT
		);
	}
}
