<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Uri;

interface Parts {
	/**
	 * Add a new part to the parts
	 * @param \Remembrall\Model\Subscribing\Part $part
	 * @param \Klapuch\Uri\Uri $uri
	 * @param string $expression
	 * @return void
	 */
	public function add(Part $part, Uri\Uri $uri, string $expression): void;

	/**
	 * Go through all the parts
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Traversable
	 */
	public function iterate(Dataset\Selection $selection): \Traversable;

	/**
	 * Counted parts
	 * @return int
	 */
	public function count(): int;
}