<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Interval representing datetime (day, month, year, hours, minutes, seconds)
 * Can find the next datetime by the given step
 */
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

	public function next(): \DateTimeInterface {
		return $this->start->add($this->step());
	}

	public function step(): \DateInterval {
		return $this->step;
	}
}