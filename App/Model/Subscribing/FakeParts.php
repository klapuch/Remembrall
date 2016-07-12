<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeParts implements Parts {
	private $parts;

	public function __construct(array $parts = []) {
		$this->parts = $parts;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		return $part;
	}

	public function replace(Part $old, Part $new): Part {
		return $new;
	}

	public function iterate(): array {
		return $this->parts;
	}

	public function remove(Part $part) {
		
	}
}