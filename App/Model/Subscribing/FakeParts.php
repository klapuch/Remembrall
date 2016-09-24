<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

/**
 * Fake
 */
final class FakeParts implements Parts {
	public function add(Part $part, Uri\Uri $uri, string $expression) {
	}

	public function iterate(): \Iterator {
		return new \ArrayIterator();
	}
}
