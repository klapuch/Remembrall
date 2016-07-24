<?php
declare(strict_types = 1);
namespace Remembrall\Model\Security;

/**
 * Strongly immutable class
 * Unchanging key for AES implementation
 */
abstract class AES {
	private $key;

	final public function __construct(string $key) {
		$this->key = $key;
	}

	final protected function key(): string {
		return $this->key;
	}
}