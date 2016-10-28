<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Output, Time, Uri, Log
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
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->subscribe(
			new Uri\FakeUri('url'),
			'//p',
			new Time\FakeInterval()
		);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
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
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->print(new Output\FakeFormat());
	}

	public function testNoExceptionDuringPrinting() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
				))->print(new Output\FakeFormat());
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
				))->iterate();
			}
		);
	}
}

(new LoggedSubscriptions())->run();
