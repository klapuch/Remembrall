<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Parts {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param string $url
	 * @param string $expression
	 * @param Interval $interval
	 * @throws Exception\DuplicateException
	 * @return Part
	 */
	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part;

	/**
	 * Remove the given part from the parts
	 * @param string $url
	 * @param string $expression
	 * @return void
	 */
	public function remove(string $url, string $expression);

	/**
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}
