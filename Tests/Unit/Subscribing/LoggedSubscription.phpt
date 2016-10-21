<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Time
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
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logger
		))->cancel();
	}

	public function testNoExceptionDuringCanceling() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(), $logger
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
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logger
		))->edit(new Time\FakeInterval());
	}

	public function testNoExceptionDuringEditing() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(), $logger
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
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			$subscription,
			$logger
		))->notify();
	}

	public function testNoExceptionDuringNotifying() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(), $logger
				))->notify();
			}
		);
	}
}

(new LoggedSubscription())->run();
