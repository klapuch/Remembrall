<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakeInterval implements Interval {
	private $start;
	private $next;
	private $step;

	public function __construct(
		\DateTimeInterface $start = null,
		Interval $next = null,
		\DateInterval $step = null
	) {
		$this->start = $start;
		$this->next = $next;
		$this->step = $step;
	}

	public function start(): \DateTimeInterface {
		return $this->start;
	}

	public function next(): Interval {
		return $this->next;
	}

	public function step(): \DateInterval {
		return $this->step;
	}
}