<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakeParts implements Parts {
	private $parts;

	public function __construct(array $parts) {
		$this->parts = $parts;
	}

	public function subscribe(Expression $expression, Interval $interval) {

	}

	public function iterate(): array {
		return $this->parts;
	}
}