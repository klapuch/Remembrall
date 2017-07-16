<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;

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
		string $language,
		Time\Interval $interval
	): void {
		if ($this->overstepped()) {
			throw new \UnexpectedValueException(
				sprintf(
					'You have reached the limit of %d subscribed parts',
					self::LIMIT
				),
				0,
				new \Exception(
					sprintf(
						'User id "%d" reached the maximum of subscribed parts',
						$this->user->id()
					)
				)
			);
		}
		$this->origin->subscribe($uri, $expression, $language, $interval);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		return $this->origin->all($selection);
	}

	/**
	 * Has the user subscribed more than X parts and overstepped the limit?
	 * @return bool
	 */
	private function overstepped(): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id 
			WHERE user_id = ?
			HAVING COUNT(parts.id) >= ?',
			[$this->user->id(), self::LIMIT]
		))->field();
	}
}