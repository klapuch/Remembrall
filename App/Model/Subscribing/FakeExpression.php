<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakeExpression implements Expression {
	private $expression;

	public function __construct(string $expression = null) {
		$this->expression = $expression;
	}

	public function match(): Part {
	}

	public function __toString() {
		return $this->expression;
	}
}