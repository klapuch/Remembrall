<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

interface Expression {
	/**
	 * Match found by the expression
	 * @throws \Remembrall\Exception\NotFoundException
	 * @return \DOMNodeList
	 */
	public function matches(): \DOMNodeList;

	/**
	 * Expression itself
	 * For XPath, it may be //div[@id=someID]
	 * @return string
	 */
	public function __toString(): string;
}