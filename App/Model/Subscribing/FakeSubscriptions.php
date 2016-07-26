<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakeSubscriptions implements Subscriptions {
	public function iterate(): array {
		return [];
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	) {

	}
}