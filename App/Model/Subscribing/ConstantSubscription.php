<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Time;

final class ConstantSubscription implements Subscription {
	private $origin;
	private $subscription;

	public function __construct(Subscription $origin, array $subscription) {
		$this->origin = $origin;
		$this->subscription = $subscription;
	}

	public function cancel(): void {
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		$this->origin->edit($interval);
	}

	public function notify(): void {
		$this->origin->notify();
	}

	public function print(Output\Format $format): Output\Format {
		return new Output\FilledFormat($format, $this->subscription);
	}
}