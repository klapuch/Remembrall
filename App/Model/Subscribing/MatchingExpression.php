<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

/**
 * Expression with always matching nodes
 */
final class MatchingExpression implements Expression {
	private $origin;

	public function __construct(Expression $origin) {
		$this->origin = $origin;
	}

	public function matches(): \DOMNodeList {
		$matches = $this->origin->matches();
		if($matches->length > 0)
			return $matches;
		throw new NotFoundException(
			'For the given expression there are no matches'
		);
	}

	public function __toString(): string {
		return (string)$this->origin;
	}
}