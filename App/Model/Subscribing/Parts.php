<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

interface Parts extends \IteratorAggregate {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @return void
	 */
	public function add(Part $part, Uri\Uri $uri, string $expression): void;
}