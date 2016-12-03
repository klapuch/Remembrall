<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

interface Parts {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @return void
	 */
	public function add(Part $part, Uri\Uri $uri, string $expression): void;

	/**
	 * Go through all the parts
	 * @return iterable
	 */
	public function iterate(): iterable;
}
