<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Output;

require __DIR__ . '/../../bootstrap.php';

final class LoggedSubscription extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringCanceling() {
		$ex = new \Exception('exceptionMessage');
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			new Subscribing\FakeSubscription($ex),
			$logger
		))->cancel();
	}

	public function testNoExceptionDuringCanceling() {
		Assert::noError(
			function() {
				$logger = $this->mockery('Tracy\ILogger');
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
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			new Subscribing\FakeSubscription($ex),
			$logger
		))->edit(new Subscribing\FakeInterval());
	}

	public function testNoExceptionDuringEditing() {
		Assert::noError(
			function() {
				$logger = $this->mockery('Tracy\ILogger');
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(), $logger
				))->edit(new Subscribing\FakeInterval());
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringPrinting() {
		$ex = new \Exception('exceptionMessage');
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscription(
			new Subscribing\FakeSubscription($ex),
			$logger
		))->print(new Output\Xml());
	}

	public function testNoExceptionDuringPrinting() {
		Assert::noError(
			function() {
				$logger = $this->mockery('Tracy\ILogger');
				(new Subscribing\LoggedSubscription(
					new Subscribing\FakeSubscription(), $logger
			))->print(new Output\Xml());
			}
		);
	}
}

(new LoggedSubscription())->run();
