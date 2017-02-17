<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Time, Uri, Access, Dataset
};

/**
 * Disallowing subscribing after more than X subscriptions
 */
final class LimitedSubscriptions implements Subscriptions {
	private const LIMIT = 5;
	private $origin;
	private $user;
	private $database;

	public function __construct(
		Subscriptions $origin,
		Access\User $user,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->user = $user;
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

	public function iterate(Dataset\Selection $selection): \Traversable {
		return $this->origin->iterate($selection);
	}

	/**
	 * Has the user subscribed more than X parts and overstepped the limit?
	 * @return bool
	 */
	private function overstepped(): bool {
		return (bool)(new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id 
			WHERE user_id IS NOT DISTINCT FROM ?
			HAVING COUNT(parts.id) >= ?',
			[$this->user->id(), self::LIMIT]
		))->field();
	}
}