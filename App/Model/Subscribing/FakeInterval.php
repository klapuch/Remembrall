<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeInterval implements Interval {
	private $start;
	private $next;
	private $step;

	public function __construct(
		\DateTimeInterface $start = null,
		\DateTimeInterface $next = null,
		\DateInterval $step = null
	) {
		$this->start = $start;
		$this->next = $next;
		$this->step = $step;
	}

	public function start(): \DateTimeInterface {
		return $this->start;
	}

	public function next(): \DateTimeInterface {
		return $this->next;
	}

	public function step(): \DateInterval {
		return $this->step;
	}
}