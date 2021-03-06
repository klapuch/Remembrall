<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

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

	public function matches(): \DOMNodeList {
		return $this->match;
	}

	public function __toString(): string {
		return $this->expression;
	}
}