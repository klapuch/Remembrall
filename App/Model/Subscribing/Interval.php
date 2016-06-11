<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Interval {
	/**
	 * When the interval should start?
	 * @return \DateTimeInterface
	 */
	public function start(): \DateTimeInterface;

	/**
	 * What is the next in the interval?
	 * @throws \OutOfRangeException
	 * @return Interval
	 */
	public function next(): self;

	/**
	 * How many units have a one step?
	 * @throws \OutOfRangeException
	 * @return \DateInterval
	 */
	public function step(): \DateInterval;
}