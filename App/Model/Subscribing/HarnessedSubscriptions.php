<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Time, Uri
};
use Remembrall\Model\Misc;

/**
 * Subscriptions harnessed by callback
 */
final class HarnessedSubscriptions implements Subscriptions {
	private $origin;
	private $callback;

	public function __construct(Subscriptions $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void {
		$this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}

	public function getIterator(): \Iterator {
		return $this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}

	public function print(Output\Format $format): array {
		return $this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}
}