<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class DateTimeInterval implements Interval {
	private $start;
	private $step;

	public function __construct(
		\DateTimeImmutable $start,
		\DateInterval $step
	) {
		$this->start = $start;
		$this->step = $step;
	}

	public function start(): \DateTimeInterface {
		return $this->start;
	}

	public function next(): Interval {
		return new self(
			$this->start->add($this->step()),
			$this->step()
		);
	}

	public function step(): \DateInterval {
		return $this->step;
	}
}