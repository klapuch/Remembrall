<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Time;
use Klapuch\Uri;

final class FakeSubscriptions implements Subscriptions {
	private $exception;
	private $subscriptions;

	public function __construct(
		\Throwable $exception = null,
		Subscription ...$subscriptions
	) {
		$this->exception = $exception;
		$this->subscriptions = $subscriptions;
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		string $language,
		Time\Interval $interval
	): void {
		if ($this->exception)
			throw $this->exception;
	}

	public function all(Dataset\Selection $selection): \Traversable {
		if ($this->exception)
			throw $this->exception;
		return new \ArrayIterator($this->subscriptions);
	}
}