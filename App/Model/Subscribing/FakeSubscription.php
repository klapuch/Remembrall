<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
    Time, Output
};

final class FakeSubscription implements Subscription {
	public function cancel() {
	}

	public function edit(Time\Interval $interval) {
    }

    public function notify() {
	}

	public function print(Output\Format $format): Output\Format {
	}
}
