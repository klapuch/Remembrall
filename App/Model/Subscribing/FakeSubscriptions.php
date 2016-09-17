<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
    Uri, Time, Output
};

final class FakeSubscriptions implements Subscriptions {
    private $exception;

    public function __construct(\Exception $exception = null) {
        $this->exception = $exception; // because mockery is useless crap
    }

	public function print(Output\Format $format): array {
        if($this->exception)
            throw $this->exception;
        return [];
	}

	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	) {
        if($this->exception)
            throw $this->exception;
	}
}
