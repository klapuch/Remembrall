<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

interface Assertion {
	/**
	 * Assert against predefined value
	 * @return void
	 */
	public function assert(): void;
}