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
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}
