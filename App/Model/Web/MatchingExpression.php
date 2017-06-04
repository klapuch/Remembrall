<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

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
		if ($matches->length > 0)
			return $matches;
		throw new \Remembrall\Exception\NotFoundException(
			'For the given expression there are no matches'
		);
	}
}