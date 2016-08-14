<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

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

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->with('visitation', $this->interval->start()->format('Y-m-d H:i'))
			->with('interval', $this->interval->step()->i);
	}
}