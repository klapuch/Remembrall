<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Expression {
	/**
	 * Match found by the expression
	 * @throws Exception\ExistenceException
	 * @return Part
	 */
	public function match(): Part;

	/**
	 * Expression itself
	 * For XPath, it may be //div[@id=someID]
	 * @return string
	 */
	public function __toString();
}