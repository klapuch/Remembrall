<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakeSubscription implements Subscription {
	private $exception;

	public function __construct(\Exception $exception = null) {
	    $this->exception = $exception;
	}

	public function cancel() {
		$this->throwException();
	}

	public function edit(Interval $interval): Subscription {
		$this->throwException();
		return $this;
	}

	public function print(): array {
		$this->throwException();
		return [];
	}

	private function throwException() {
		if($this->exception !== null)
			throw $this->exception;
	}
}