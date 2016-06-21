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
	 * @throws Exception\ExistenceException
	 * @return void
	 */
	public function replace(Part $old, Part $new);

	/**
	 * Remove the given part from the parts
	 * @param Part $part
	 * @return void
	 */
	public function remove(Part $part);

	/**
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}
