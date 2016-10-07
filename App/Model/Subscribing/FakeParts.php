<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

/**
 * Fake
 */
final class FakeParts implements Parts {
	private $exception;

	public function __construct(\Throwable $exception = null) {
	    $this->exception = $exception;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		if($this->exception)
			throw $this->exception;
	}

	public function iterate(): \Iterator {
		if($this->exception)
			throw $this->exception;
		return new \ArrayIterator();
	}
}