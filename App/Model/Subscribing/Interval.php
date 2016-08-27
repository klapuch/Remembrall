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
	 * @return \DateTimeInterface
	 */
	public function next(): \DateTimeInterface;

	/**
	 * How many units have a one step?
	 * @throws \OutOfRangeException
	 * @return int
	 */
	public function step(): int;
}