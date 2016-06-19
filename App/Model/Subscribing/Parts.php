<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Parts {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param Interval $interval
	 * @throws Exception\DuplicateException
	 * @return void
	 */
	public function subscribe(Part $part, Interval $interval);

	/**
	 * Replace the old part with the new one
	 * @param Part $old
	 * @param Part $new
	 * @return void
	 */
	public function replace(Part $old, Part $new);

	/**
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}
