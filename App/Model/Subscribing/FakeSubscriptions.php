<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Time;
use Klapuch\Uri;
use Klapuch\Dataset;

final class FakeSubscriptions implements Subscriptions {
	private $exception;

	public function __construct(?\Throwable $exception = null) {
		$this->exception = $exception;
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void {
		if($this->exception)
			throw $this->exception;
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		if($this->exception)
			throw $this->exception;
		return new \ArrayIterator();
	}
}