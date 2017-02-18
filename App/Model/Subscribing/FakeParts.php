<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Uri;

/**
 * Fake
 */
final class FakeParts implements Parts {
	private $exception;

	public function __construct(?\Throwable $exception = null) {
		$this->exception = $exception;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		if($this->exception)
			throw $this->exception;
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		if($this->exception)
			throw $this->exception;
		return new \ArrayIterator();
	}

	public function print(Output\Format $format): array {
		if($this->exception)
			throw $this->exception;
		return [];
	}
}