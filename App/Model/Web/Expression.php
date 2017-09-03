<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

interface Expression {
	/**
	 * Match found by the expression
	 * @throws \UnexpectedValueException
	 * @return \DOMNodeList
	 */
	public function matches(): \DOMNodeList;
}