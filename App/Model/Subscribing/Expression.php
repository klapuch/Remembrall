<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Expression {
	/**
	 * Match found by the expression
	 * @throws Exception\ExistenceException
	 * @return \DOMNodeList
	 */
	public function match(): \DOMNodeList;

	/**
	 * Expression itself
	 * For XPath, it may be //div[@id=someID]
	 * @return string
	 */
	public function __toString();
}