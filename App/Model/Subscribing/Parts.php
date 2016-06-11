<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Parts {
	/**
	 * Add a new part to the parts by the given expression
	 * @param Expression $expression
	 * @return void
	 */
	public function subscribe(Expression $expression);

	/**
	 * Go through all the parts
	 * @return Part[]
	 */
	public function iterate(): array;
}