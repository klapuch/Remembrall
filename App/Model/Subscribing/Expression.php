<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

interface Expression {
	/**
	 * Match found by the expression
	 * @throws NotFoundException
	 * @return \DOMNodeList
	 */
	public function match(): \DOMNodeList;

	/**
	 * Expression itself
	 * For XPath, it may be //div[@id=someID]
	 * @return string
	 */
	public function __toString(): string;
}