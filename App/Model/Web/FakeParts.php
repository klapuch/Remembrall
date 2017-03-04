<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Uri;

/**
 * Fake
 */
final class FakeParts implements Parts {
	private $exception;
	private $parts;

	public function __construct(\Throwable $exception = null, Part ...$parts) {
		$this->exception = $exception;
		$this->parts = $parts;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		if($this->exception)
			throw $this->exception;
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		if($this->exception)
			throw $this->exception;
		return new \ArrayIterator($this->parts);
	}

	public function count(): int {
		return count($this->parts);
	}
}