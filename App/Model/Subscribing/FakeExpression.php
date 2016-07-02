<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeExpression implements Expression {
	private $expression;
	private $match;

	public function __construct(
		string $expression = null,
		\DOMNodeList $match = null
	) {
		$this->expression = $expression;
		$this->match = $match;
	}

	public function match(): \DOMNodeList {
		return $this->match;
	}

	public function __toString() {
		return $this->expression;
	}
}