<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

/**
 * XPath expression must be only valid
 * By "valid" is meant that it must produce some nodes
 */
final class ValidXPathExpression implements Expression {
	private $origin;

	public function __construct(Expression $origin) {
		$this->origin = $origin;
	}

	public function matches(): \DOMNodeList {
		$nodes = $this->origin->matches();
		if($nodes->length > 0)
			return $nodes;
		throw new NotFoundException(
			'For the given XPath expression there are no matches'
		);
	}

	public function __toString(): string {
		return (string)$this->origin;
	}
}
