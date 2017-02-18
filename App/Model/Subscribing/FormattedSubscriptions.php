<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Iterator;
use Klapuch\Time;
use Klapuch\Uri;
use Texy;

/**
 * Formatted subscriptions
 */
final class FormattedSubscriptions implements Subscriptions {
	private $origin;
	private $texy;

	public function __construct(Subscriptions $origin, Texy\Texy $texy) {
		$this->origin = $origin;
		$this->texy = $texy;
	}

	public function subscribe(
		Uri\Uri $url,
		string $expression,
		Time\Interval $interval
	): void {
		$this->origin->subscribe($url, $expression, $interval);
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		return new Iterator\MappedIterator(
			$this->origin->iterate($selection),
			function(Subscription $subscription): Subscription {
				return new FormattedSubscription($subscription, $this->texy);
			}
		);
	}
}