<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * XPath expression parsed from the given page
 */
final class XPathExpression implements Expression {
	private $expression;
	private $page;

	public function __construct(Page $page, string $expression) {
		$this->page = $page;
		$this->expression = $expression;
	}

	public function match(): \DOMNodeList {
		return (new \DOMXPath(
			$this->page->content()
		))->query($this->expression);
	}

	public function __toString(): string {
		return $this->expression;
	}
}