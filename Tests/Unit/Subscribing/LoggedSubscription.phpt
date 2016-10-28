<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Time, Log
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedSubscription extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringCanceling() {
		$ex = new \Exception('exceptionMessage');
		$subscription = new Subscribing\FakeSubscription($ex);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logs
		))->cancel();
	}

	public function testNoExceptionDuringCanceling() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(),
					new Log\FakeLogs()
				))->cancel();
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringEditing() {
		$ex = new \Exception('exceptionMessage');
		$subscription = new Subscribing\FakeSubscription($ex);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logs
		))->edit(new Time\FakeInterval());
	}

	public function testNoExceptionDuringEditing() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(),
					new Log\FakeLogs()
				))->edit(new Time\FakeInterval());
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringNotifying() {
		$ex = new \Exception('exceptionMessage');
		$subscription = new Subscribing\FakeSubscription($ex);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logs
		))->notify();
	}

	public function testNoExceptionDuringNotifying() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(),
					new Log\FakeLogs()
				))->notify();
			}
		);
	}
}

(new LoggedSubscription())->run();
