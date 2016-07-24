<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\Access;

/**
 * Disallow subscribing after more than X parts
 */
final class LimitedParts implements Parts {
	const LIMIT = 5;
	private $database;
	private $subscriber;
	private $origin;

	public function __construct(
		Dibi\Connection $database,
		Access\Subscriber $subscriber,
		Parts $origin
	) {
		$this->database = $database;
		$this->subscriber = $subscriber;
		$this->origin = $origin;
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		if($this->overstepped()) {
			throw new \OverflowException(
				sprintf(
					'You have reached limit of %d subscribed parts',
					self::LIMIT
				)
			);
		}
		return $this->origin->subscribe($part, $url, $expression, $interval);
	}

	public function remove(string $url, string $expression) {
		$this->origin->remove($url, $expression);
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
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.id 
			WHERE subscriber_id = ?
			HAVING COUNT(parts.id) >= ?',
			$this->subscriber->id(),
			self::LIMIT
		);
	}
}
