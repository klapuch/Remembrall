<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

final class ConstantSubscription implements Subscription {
	private $origin;
	private $interval;
	private $lastUpdate;

	public function __construct(
		Subscription $origin,
		Interval $interval,
		\DateTimeImmutable $lastUpdate
	) {
		$this->origin = $origin;
		$this->interval = $interval;
		$this->lastUpdate = $lastUpdate;
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
			->with('interval', $this->interval->step()->i)
			->with('lastUpdate', $this->lastUpdate->format('Y-m-d H:i'));
	}
}