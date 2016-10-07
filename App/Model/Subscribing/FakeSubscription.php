<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Time;

final class FakeSubscription implements Subscription {
	private $exception;

	public function __construct(\Throwable $exception = null) {
	    $this->exception = $exception;
	}

	public function cancel(): void {
		if($this->exception)
			throw $this->exception;
	}

	public function edit(Time\Interval $interval): void {
		if($this->exception)
			throw $this->exception;
    }

    public function notify(): void {
		if($this->exception)
			throw $this->exception;
	}
}