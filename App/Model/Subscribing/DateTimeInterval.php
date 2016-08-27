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
		return $this->start->add($this->step);
	}

	public function step(): int {
		if($this->transferable($this->step))
			return $this->toSecond($this->step);
		throw new \OutOfRangeException(
			'Months or years can not be precisely transferred'
		);
	}

	/**
	 * Transferred step to the seconds
	 * @param \DateInterval $step
	 * @return int
	 */
	private function toSecond(\DateInterval $step): int {
		return $step->d * 86400
		+ $step->h * 3600
		+ $step->i * 60
		+ $step->s;
	}

	/**
	 * Can be the step precisely transferred to the seconds?
	 * Years and months differs and can not be precisely calculated
	 * @param \DateInterval $step
	 * @return bool
	 */
	private function transferable(\DateInterval $step): bool {
		return $step->m === 0 && $step->y === 0;
	}
}