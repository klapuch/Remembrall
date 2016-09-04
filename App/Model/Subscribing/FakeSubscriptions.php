<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
    Uri, Time
};

final class FakeSubscriptions implements Subscriptions {
	public function iterate(): array {
		return [];
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	) {

	}
}
