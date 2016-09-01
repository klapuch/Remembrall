<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

/**
 * Fake
 */
final class FakeParts implements Parts {
	private $parts;

	public function __construct(array $parts = []) {
		$this->parts = $parts;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): Part {
		return $part;
	}

	public function iterate(): array {
		return $this->parts;
	}
}