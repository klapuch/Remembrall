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

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		return $part;
	}

	public function remove(string $url, string $expression) {
	}

	public function iterate(): array {
		return $this->parts;
	}
}