<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Output, Time, Uri
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedSubscriptions extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringSubscribing() {
		$ex = new \Exception('exceptionMessage');
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logger
		))->subscribe(
			new Uri\FakeUri('url'),
			'//p',
			new Time\FakeInterval()
		);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(), $logger
				))->subscribe(
					new Uri\FakeUri('url'),
					'//p',
					new Time\FakeInterval()
				);
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringPrinting() {
		$ex = new \Exception('exceptionMessage');
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logger
		))->print(new Output\FakeFormat());
	}

	public function testNoExceptionDuringPrinting() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(), $logger
				))->print(new Output\FakeFormat());
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logger
		))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(), $logger
				))->iterate();
			}
		);
	}
}

(new LoggedSubscriptions())->run();
