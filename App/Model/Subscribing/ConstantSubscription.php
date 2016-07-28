<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class ConstantSubscription implements Subscription {
	private $origin;
	private $interval;

	public function __construct(Subscription $origin, Interval $interval) {
		$this->origin = $origin;
		$this->interval = $interval;
	}

	public function cancel() {
		$this->origin->cancel();
	}

	public function edit(Interval $interval): Subscription {
		return $this->origin->edit($interval);
	}

	public function print(): array {
		return $this->origin->print() + [
			'interval' => $this->interval,
		];
	}
}