<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

final class FakeSubscriptions implements Subscriptions {
	public function iterate(): array {
		return [];
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Interval $interval
	) {

	}
}