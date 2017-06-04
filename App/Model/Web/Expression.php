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
}