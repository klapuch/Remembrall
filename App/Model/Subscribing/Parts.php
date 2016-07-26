<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Parts {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param string $url
	 * @param string $expression
	 * @return Part
	 */
	public function add(Part $part, string $url, string $expression): Part;

	/**
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}
