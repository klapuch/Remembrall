<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

final class ValidXPathExpression implements Expression {
	private $origin;
	private $page;

	public function __construct(Expression $origin, Page $page) {
		$this->origin = $origin;
		$this->page = $page;
	}

	public function match(): Part {
		$domX = new \DOMXPath($this->page->content());
		if($domX->query((string)$this)->length > 0)
			return $this->origin->match();
		throw new Exception\ExistenceException(
			sprintf(
				'XPath expression "%s" does not exist on the "%s" page',
				(string)$this,
				$this->page->url()
			)
		);
	}

	public function __toString() {
		return (string)$this->origin;
	}
}