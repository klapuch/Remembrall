<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Time;

final class FakeSubscription implements Subscription {
	private $exception;
	private $print;

	public function __construct(\Throwable $exception = null, Output\Format $print = null) {
		$this->exception = $exception;
		$this->print = $print;
	}

	public function cancel(): void {
		if ($this->exception)
			throw $this->exception;
	}

	public function edit(Time\Interval $interval): void {
		if ($this->exception)
			throw $this->exception;
	}

	public function notify(): void {
		if ($this->exception)
			throw $this->exception;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->print ?: $format;
	}
}